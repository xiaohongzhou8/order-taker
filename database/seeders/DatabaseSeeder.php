<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ZipArchive;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const MenuSourceUrl = 'https://drive.usercontent.google.com/download?id=1NipSK_ZEEAskgjyJ12ZkVhpJeRmq6ypB&export=download&confirm=t';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ],
        );

        $menuItems = $this->downloadMenuItems();

        foreach ($menuItems as $menuItem) {
            MenuItem::query()->updateOrCreate(
                ['item_number' => $menuItem['item_number']],
                $menuItem,
            );
        }

        MenuItem::query()
            ->whereNotIn('item_number', array_column($menuItems, 'item_number'))
            ->delete();
    }

    /**
     * @return list<array{item_number: string, name: string, price: float}>
     */
    private function downloadMenuItems(): array
    {
        $response = Http::connectTimeout(3)
            ->timeout(20)
            ->get(self::MenuSourceUrl)
            ->throw();

        $temporaryFile = tempnam(sys_get_temp_dir(), 'menu-items-');

        if ($temporaryFile === false) {
            throw new RuntimeException('Unable to create a temporary menu spreadsheet.');
        }

        try {
            if (file_put_contents($temporaryFile, $response->body()) === false) {
                throw new RuntimeException('Unable to save the menu spreadsheet.');
            }

            return $this->parseMenuSpreadsheet($temporaryFile);
        } finally {
            unlink($temporaryFile);
        }
    }

    /**
     * @return list<array{item_number: string, name: string, price: float}>
     */
    private function parseMenuSpreadsheet(string $spreadsheetPath): array
    {
        $archive = new ZipArchive;

        if ($archive->open($spreadsheetPath) !== true) {
            throw new RuntimeException('The downloaded menu source is not a valid Excel workbook.');
        }

        try {
            $worksheet = $this->loadXml($archive->getFromName('xl/worksheets/sheet1.xml'));
            $sharedStrings = $this->sharedStrings($archive);
        } finally {
            $archive->close();
        }

        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $items = [];
        $sheetData = $worksheet->children($namespace)->sheetData;
        $pendingPackage = null;

        foreach ($sheetData->children($namespace)->row as $row) {
            $cells = [];

            foreach ($row->children($namespace)->c as $cell) {
                $attributes = $cell->attributes();
                $column = preg_replace('/\d+/', '', (string) $attributes['r']);
                $value = (string) $cell->children($namespace)->v;

                if ((string) $attributes['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }

                $cells[$column] = trim($value);
            }

            if (
                isset($cells['A'], $cells['B'])
                && $cells['A'] !== ''
                && ($cells['C'] ?? '') === ''
                && is_numeric($cells['A'])
                && (float) $cells['A'] === (float) (int) $cells['A']
            ) {
                $pendingPackage = [
                    'item_number' => (string) (int) $cells['A'],
                    'name' => $cells['B'],
                ];

                continue;
            }

            if (
                $pendingPackage !== null
                && isset($cells['B'], $cells['C'])
                && preg_match('/^Voor\s+(\d+)\s+personen$/iu', $cells['B'], $matches) === 1
                && is_numeric($cells['C'])
            ) {
                $persons = (int) $matches[1];
                $itemNumber = $pendingPackage['item_number'].($persons > 2 ? $this->personSuffix($persons) : '');

                if (! isset($items[$itemNumber])) {
                    $items[$itemNumber] = [
                        'item_number' => $itemNumber,
                        'name' => $pendingPackage['name'].' ('.$persons.' personen)',
                        'price' => (float) $cells['C'],
                    ];
                }

                continue;
            }

            if (! isset($cells['A'], $cells['B'], $cells['C']) || $cells['A'] === '' || ! is_numeric($cells['C'])) {
                continue;
            }

            $pendingPackage = null;
            $itemNumber = $cells['A'];

            if (is_numeric($itemNumber)) {
                if ((float) $itemNumber !== (float) (int) $itemNumber) {
                    continue;
                }

                $itemNumber = (string) (int) $itemNumber;
            }

            if (isset($items[$itemNumber])) {
                continue;
            }

            $items[$itemNumber] = [
                'item_number' => $itemNumber,
                'name' => $cells['B'],
                'price' => (float) $cells['C'],
            ];
        }

        if ($items === []) {
            throw new RuntimeException('The menu spreadsheet did not contain any menu items.');
        }

        return array_values($items);
    }

    private function personSuffix(int $persons): string
    {
        $index = $persons - 3;
        $suffix = '';

        do {
            $suffix = chr(97 + ($index % 26)).$suffix;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $suffix;
    }

    /**
     * @return list<string>
     */
    private function sharedStrings(ZipArchive $archive): array
    {
        $sharedStringsXml = $archive->getFromName('xl/sharedStrings.xml');

        if ($sharedStringsXml === false) {
            return [];
        }

        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $sharedStrings = [];

        foreach ($this->loadXml($sharedStringsXml)->children($namespace)->si as $string) {
            $textNodes = $string->xpath('.//*[local-name() = "t"]') ?: [];
            $sharedStrings[] = implode('', array_map(
                static fn ($text): string => (string) $text,
                $textNodes,
            ));
        }

        return $sharedStrings;
    }

    private function loadXml(string|false $xml): \SimpleXMLElement
    {
        if ($xml === false) {
            throw new RuntimeException('The menu spreadsheet is missing required worksheet data.');
        }

        $document = simplexml_load_string($xml, options: LIBXML_NONET);

        if ($document === false) {
            throw new RuntimeException('The menu spreadsheet contains invalid worksheet data.');
        }

        return $document;
    }
}
