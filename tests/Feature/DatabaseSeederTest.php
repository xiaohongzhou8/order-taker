<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_unique_menu_items_from_the_shared_spreadsheet(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://drive.usercontent.google.com/download?id=1NipSK_ZEEAskgjyJ12ZkVhpJeRmq6ypB&export=download&confirm=t' => Http::response($this->spreadsheetContents()),
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('menu_items', 6);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '1',
            'name' => 'Tomatensoep',
            'price' => '3.50',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '2',
            'name' => 'Kroepoek',
            'price' => '2.50',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => 'A7',
            'name' => 'Chicken with letters',
            'price' => '4.00',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '184',
            'name' => 'CHINESE RICE TABLE (2 personen)',
            'price' => '40.00',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '184a',
            'name' => 'CHINESE RICE TABLE (3 personen)',
            'price' => '60.00',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '184b',
            'name' => 'CHINESE RICE TABLE (4 personen)',
            'price' => '80.00',
        ]);

        Http::assertSent(static fn ($request): bool => $request->url() === 'https://drive.usercontent.google.com/download?id=1NipSK_ZEEAskgjyJ12ZkVhpJeRmq6ypB&export=download&confirm=t');
    }

    public function test_seeder_does_not_create_orders_or_modify_existing_orders(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://drive.usercontent.google.com/download?id=1NipSK_ZEEAskgjyJ12ZkVhpJeRmq6ypB&export=download&confirm=t' => Http::response($this->spreadsheetContents()),
        ]);
        $existingUser = User::factory()->create();
        $existingOrder = $existingUser->orders()->create([
            'order_number' => 'ORD-1',
            'customer_name' => 'Customer order',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('orders', [
            'id' => $existingOrder->id,
            'order_number' => 'ORD-1',
            'customer_name' => 'Customer order',
        ]);
        $this->assertDatabaseCount('orders', 1);
    }

    private function spreadsheetContents(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'menu-spreadsheet-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary spreadsheet.');
        }

        $archive = new ZipArchive;
        $archiveOpen = false;

        try {
            if ($archive->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create a temporary spreadsheet.');
            }

            $archiveOpen = true;
            $archive->addFromString('xl/sharedStrings.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <si><t>SOEPEN</t></si>
    <si><t>Tomatensoep</t></si>
    <si><t>BIJGERECHTEN</t></si>
    <si><t>Kroepoek</t></si>
    <si><t>Lunch deal</t></si>
    <si><t>A7</t></si>
    <si><t>Chicken with letters</t></si>
    <si><t>CHINESE RICE TABLE</t></si>
    <si><t>Voor 2 personen</t></si>
    <si><t>Voor 3 personen</t></si>
    <si><t>Voor 4 personen</t></si>
</sst>
XML);
            $archive->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>
        <row r="1"><c r="B1" t="s"><v>0</v></c><c r="C1" t="s"><v>0</v></c></row>
        <row r="2"><c r="A2"><v>1</v></c><c r="B2" t="s"><v>1</v></c><c r="C2"><v>3.5</v></c></row>
        <row r="3"><c r="B3" t="s"><v>2</v></c></row>
        <row r="4"><c r="A4"><v>2</v></c><c r="B4" t="s"><v>3</v></c><c r="C4"><v>2.5</v></c></row>
        <row r="5"><c r="A5"><v>1</v></c><c r="B5" t="s"><v>4</v></c><c r="C5"><v>10</v></c></row>
        <row r="6"><c r="A6" t="s"><v>5</v></c><c r="B6" t="s"><v>6</v></c><c r="C6"><v>4</v></c></row>
        <row r="7"><c r="A7"><v>184</v></c><c r="B7" t="s"><v>7</v></c></row>
        <row r="8"><c r="B8" t="s"><v>8</v></c><c r="C8"><v>40</v></c></row>
        <row r="9"><c r="B9" t="s"><v>9</v></c><c r="C9"><v>60</v></c></row>
        <row r="10"><c r="B10" t="s"><v>10</v></c><c r="C10"><v>80</v></c></row>
    </sheetData>
</worksheet>
XML);
            $archive->close();
            $archiveOpen = false;

            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new RuntimeException('Unable to read the temporary spreadsheet.');
            }

            return $contents;
        } finally {
            if ($archiveOpen) {
                $archive->close();
            }

            unlink($path);
        }
    }
}
