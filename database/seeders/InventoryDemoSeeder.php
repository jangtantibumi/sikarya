<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Category;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Uom;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseZone;
use App\Models\Inventory\WarehouseRack;
use App\Models\Inventory\WarehouseBin;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockSummary;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockIn;
use App\Models\Inventory\StockInLine;
use App\Models\Inventory\StockOut;
use App\Models\Inventory\StockOutLine;
use App\Models\Inventory\Transfer;
use App\Models\Inventory\TransferLine;
use App\Models\Inventory\Adjustment;
use App\Models\Inventory\AdjustmentLine;
use App\Models\Inventory\CycleCount;
use App\Models\Inventory\CycleCountLine;
use App\Models\Inventory\Reservation;
use App\Models\Inventory\ReservationLine;
use App\Models\Inventory\Picking;
use App\Models\Inventory\PickingLine;
use App\Models\Inventory\Packing;
use App\Models\Inventory\PackingLine;
use App\Models\Inventory\Delivery;
use App\Models\Inventory\DeliveryLine;
use App\Models\Inventory\SerialNumber;
use App\Models\Inventory\BatchNumber;
use App\Models\Inventory\Barcode;
use App\Models\Inventory\InventorySetting;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');

        // Clear existing demo tables
        Category::truncate();
        Brand::truncate();
        Uom::truncate();
        Warehouse::truncate();
        WarehouseZone::truncate();
        WarehouseRack::truncate();
        WarehouseBin::truncate();
        Item::truncate();
        StockSummary::truncate();
        StockMovement::truncate();
        StockIn::truncate();
        StockInLine::truncate();
        StockOut::truncate();
        StockOutLine::truncate();
        Transfer::truncate();
        TransferLine::truncate();
        Adjustment::truncate();
        AdjustmentLine::truncate();
        CycleCount::truncate();
        CycleCountLine::truncate();
        Reservation::truncate();
        ReservationLine::truncate();
        Picking::truncate();
        PickingLine::truncate();
        Packing::truncate();
        PackingLine::truncate();
        Delivery::truncate();
        DeliveryLine::truncate();
        SerialNumber::truncate();
        BatchNumber::truncate();
        Barcode::truncate();
        InventorySetting::truncate();

        DB::statement('PRAGMA foreign_keys = ON;');

        // 1. Categories (15)
        $categoriesData = [
            ['name' => 'Daging & Unggas', 'code' => 'CAT-001', 'description' => 'Bahan mentah olahan daging sapi dan ayam'],
            ['name' => 'Bumbu & Rempah', 'code' => 'CAT-002', 'description' => 'Bumbu dapur, rempah-rempah basah dan kering'],
            ['name' => 'Minyak & Lemak', 'code' => 'CAT-003', 'description' => 'Minyak goreng, mentega, dan margarin'],
            ['name' => 'Tepung & Olahan', 'code' => 'CAT-004', 'description' => 'Berbagai jenis tepung terigu, tapioka, beras'],
            ['name' => 'Dairy & Keju', 'code' => 'CAT-005', 'description' => 'Susu segar, keju cheddar, mozzarella, krim'],
            ['name' => 'Minuman & Syrup', 'code' => 'CAT-006', 'description' => 'Syrup perasa, teh, kopi, dan konsentrat'],
            ['name' => 'Kemasan & Packaging', 'code' => 'CAT-007', 'description' => 'Paper cup, botol, plastik wrap, sedotan'],
            ['name' => 'Sayuran Segar', 'code' => 'CAT-008', 'description' => 'Sayur-sayuran segar kebutuhan harian'],
            ['name' => 'Seafood & Ikan', 'code' => 'CAT-009', 'description' => 'Udang, cumi, dan filet ikan segar'],
            ['name' => 'Frozen Food', 'code' => 'CAT-010', 'description' => 'Nugget, sosis, kentang beku, dan dimsum'],
            ['name' => 'Saus & Sambal', 'code' => 'CAT-011', 'description' => 'Saus tomats, saus sambal, saus tiram, BBQ'],
            ['name' => 'Topping & Dessert', 'code' => 'CAT-012', 'description' => 'Boba, jelly, choco chips, whipping cream'],
            ['name' => 'Bakery & Roti', 'code' => 'CAT-013', 'description' => 'Roti burger, roti tawar, tortilla wrap'],
            ['name' => 'Kebersihan & Sanitasi', 'code' => 'CAT-014', 'description' => 'Sabun cuci piring, sanitizer, pembersih lantai'],
            ['name' => 'Perlengkapan Barista', 'code' => 'CAT-015', 'description' => 'Filter kertas, tamper, pitcher milk'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[] = Category::create($c);
        }

        // 2. Brands (15)
        $brandsData = [
            ['name' => 'Diamond', 'code' => 'BRD-001', 'description' => 'Produk Olahan Susu & Keju'],
            ['name' => 'Anchor', 'code' => 'BRD-002', 'description' => 'Butter & Cream Import'],
            ['name' => 'Sasa', 'code' => 'BRD-003', 'description' => 'Bumbu Garam & Penyedap'],
            ['name' => 'Bimoli', 'code' => 'BRD-004', 'description' => 'Minyak Goreng Sawit'],
            ['name' => 'Bogasari', 'code' => 'BRD-005', 'description' => 'Tepung Terigu Kuliner'],
            ['name' => 'Greenfields', 'code' => 'BRD-006', 'description' => 'Susu UHT & Fresh Milk'],
            ['name' => 'ABC', 'code' => 'BRD-007', 'description' => 'Kecap & Sares Sambal'],
            ['name' => 'Sosro', 'code' => 'BRD-008', 'description' => 'Teh Celup & Cair'],
            ['name' => 'Indofood', 'code' => 'BRD-009', 'description' => 'Bumbu Racik & Bahan Masakan'],
            ['name' => 'Kraft', 'code' => 'BRD-010', 'description' => 'Keju Olahan Premium'],
            ['name' => 'Knorr', 'code' => 'BRD-011', 'description' => 'Kaldu Ayam & Sapi Kuah'],
            ['name' => 'Fiesta', 'code' => 'BRD-012', 'description' => 'Olahan Daging & Ayam Beku'],
            ['name' => 'Walls', 'code' => 'BRD-013', 'description' => 'Es Krim & Dessert'],
            ['name' => 'SilverQueen', 'code' => 'BRD-014', 'description' => 'Cokelat Bumbu & Topping'],
            ['name' => 'Torabika', 'code' => 'BRD-015', 'description' => 'Bubuk Kopi & Creamer'],
        ];

        $brands = [];
        foreach ($brandsData as $b) {
            $brands[] = Brand::create($b);
        }

        // 3. UoM (10)
        $uomsData = [
            ['name' => 'Kilogram', 'code' => 'UOM-KG', 'symbol' => 'kg'],
            ['name' => 'Gram', 'code' => 'UOM-GR', 'symbol' => 'g'],
            ['name' => 'Liter', 'code' => 'UOM-LTR', 'symbol' => 'L'],
            ['name' => 'Pack', 'code' => 'UOM-PCK', 'symbol' => 'pck'],
            ['name' => 'Box', 'code' => 'UOM-BOX', 'symbol' => 'box'],
            ['name' => 'Pieces', 'code' => 'UOM-PCS', 'symbol' => 'pcs'],
            ['name' => 'Roll', 'code' => 'UOM-RLL', 'symbol' => 'roll'],
            ['name' => 'Botol', 'code' => 'UOM-BTL', 'symbol' => 'btl'],
            ['name' => 'Can/Kaleng', 'code' => 'UOM-CAN', 'symbol' => 'can'],
            ['name' => 'Karton', 'code' => 'UOM-KRT', 'symbol' => 'krt'],
        ];

        $uoms = [];
        foreach ($uomsData as $u) {
            $uoms[] = Uom::create($u);
        }

        // 4. Warehouses (5) & Hierarchical Locations
        $warehousesData = [
            ['name' => 'Gudang Utama Jakarta', 'code' => 'WH-JKT', 'address' => 'Jl. Kebon Jeruk No. 45, Jakarta Barat', 'phone' => '021-5551234', 'email' => 'wh.jkt@suba-erp.com', 'manager_name' => 'Budi Santoso'],
            ['name' => 'Gudang Hub Surabaya', 'code' => 'WH-SBY', 'address' => 'Jl. Rungkut Industri III No. 12, Surabaya', 'phone' => '031-8445678', 'email' => 'wh.sby@suba-erp.com', 'manager_name' => 'Siti Nurhaliza'],
            ['name' => 'Cold Storage Central', 'code' => 'WH-COLD', 'address' => 'Kawasan Industri Cikarang Blok B-2', 'phone' => '021-8991122', 'email' => 'coldstorage@suba-erp.com', 'manager_name' => 'Hendrik Setiawan'],
            ['name' => 'Dry Store Bandung', 'code' => 'WH-BDG', 'address' => 'Jl. Soekarno Hatta No. 210, Bandung', 'phone' => '022-7339988', 'email' => 'wh.bdg@suba-erp.com', 'manager_name' => 'Agus Pratama'],
            ['name' => 'Distribution Center Depok', 'code' => 'WH-DPK', 'address' => 'Jl. Margonda Raya No. 88, Depok', 'phone' => '021-7720011', 'email' => 'wh.dpk@suba-erp.com', 'manager_name' => 'Dewi Lestari'],
        ];

        $warehouses = [];
        $bins = [];
        foreach ($warehousesData as $whIndex => $w) {
            $warehouse = Warehouse::create($w);
            $warehouses[] = $warehouse;

            // Create Zones
            $zoneA = WarehouseZone::create(['warehouse_id' => $warehouse->id, 'name' => 'Zone A - Receiving', 'code' => 'Z-A', 'description' => 'Area penerimaan barang masuk']);
            $zoneB = WarehouseZone::create(['warehouse_id' => $warehouse->id, 'name' => 'Zone B - Storage Main', 'code' => 'Z-B', 'description' => 'Area penyimpanan utama']);

            // Create Racks
            $rack1 = WarehouseRack::create(['zone_id' => $zoneA->id, 'name' => 'Rack A1', 'code' => 'R-A1']);
            $rack2 = WarehouseRack::create(['zone_id' => $zoneB->id, 'name' => 'Rack B1', 'code' => 'R-B1']);

            // Create Bins
            $bin1 = WarehouseBin::create(['rack_id' => $rack1->id, 'name' => 'Bin A1-01', 'code' => 'BIN-A1-01']);
            $bin2 = WarehouseBin::create(['rack_id' => $rack2->id, 'name' => 'Bin B1-01', 'code' => 'BIN-B1-01']);
            
            $bins[] = $bin1;
            $bins[] = $bin2;
        }

        // 5. Items (100 F&B items)
        $fnbItemsList = [
            'Ayam Fillet Dada', 'Daging Sapi Slice 500g', 'Saus BBQ Original', 'Minyak Goreng Sawit 2L', 'Tepung Terigu Cakra Kembar',
            'Keju Cheddar Block 2kg', 'Susu UHT Full Cream 1L', 'Paper Cup 16oz', 'Sedotan Steril Plastik', 'Botol Syrup Vanilla 750ml',
            'Daging Ayam Paha Boneless', 'Daging Sapi Ribeye Wagyu', 'Saus Tomat Pouch 1kg', 'Minyak Wijen 600ml', 'Tepung Tapioka Cap Tani',
            'Keju Mozzarella Melt 1kg', 'Susu Evaporasi Kaleng', 'Paper Cup 12oz Cold', 'Plastic Straw Flexible', 'Botol Syrup Caramel 750ml',
            'Ayam Giling Segar 1kg', 'Daging Sapi Tenderloin', 'Saus Sambal Extra Pedas', 'Mentega Salted Anchor 227g', 'Tepung Beras Rose Brand',
            'Krim Kocok Whip Cream 1L', 'Susu Kental Manis 500g', 'Paper Bowl 800ml', 'Sendok Plastik Bening', 'Botol Syrup Hazelnut 750ml',
            'Ayam Utuh Potong 10', 'Daging Sapi Sirloin Aus', 'Saus Tiram Premium 1L', 'Margarin Multi Guna 1kg', 'Tepung Maizena 500g',
            'Keju Slice Sandwich 12s', 'Susu Fresh Milk Pasteurisasi', 'Lunch Box Kraft Medium', 'Garpu Plastik Hitam', 'Botol Syrup Pandan 750ml',
            'Sayur Lettuce Fresh 1kg', 'Bawang Bomba Mulus 1kg', 'Cabai Merah Keriting 1kg', 'Garul Dapur Halus 1kg', 'Gula Pasir Kristal 1kg',
            'Kopi Arabica Beans 1kg', 'Teh Hitam Celup Jumbo', 'Boba Tapioka Pearl 1kg', 'Jelly Grass Cincau 1kg', 'Choco Chips Dark 500g',
            'Roti Burger Sesame 6s', 'Roti Tawar Thick Slice', 'Tortilla Wrap 10 Inch', 'Sosis Sapi Cocktail 1kg', 'Nugget Ayam Crispy 1kg',
            'Kentang Shoestring Beku 2.5kg', 'Dimsum Ayam Udang 50s', 'Udang Kupas Frozen 1kg', 'Filet Ikan Dori Beku 1kg', 'Cumi Ring Frozen 1kg',
            'Sabun Cuci Piring 5L', 'Hand Sanitizer Gel 5L', 'Pembersih Lantai Pine 5L', 'Tissue Napkin Resto 100s', 'Aluminium Foil Roll 75m',
            'Cling Wrap Roll 500m', 'Kertas Kasir Thermal 80mm', 'Sarung Tangan Nitrile M', 'Masker 3ply Box 50s', 'Kantong Plastik HDPE L',
            'Kopi Robusta Lampung 1kg', 'Bubuk Matcha Japan 500g', 'Bubuk Taro Creamy 1kg', 'Bubuk Chocolate Dark 1kg', 'Bubuk Red Velvet 1kg',
            'Syrup Hazelnut Sugar Free', 'Syrup Brown Sugar 1L', 'Condensed Milk Pouch 1kg', 'Creamer Bubuk Premium 1kg', 'Gula Cair Fruktosa 5kg',
            'Bawang Putih Kupas 1kg', 'Bawang Merah Super 1kg', 'Daun Bawang Fresh 500g', 'Tomat Merah Fresh 1kg', 'Jeruk Nipis Peras 1kg',
            'Mayonnaise Original 1kg', 'Thousand Island Sauce 1kg', 'Saus Mustard Yellow 500g', 'Teriyaki Sauce Japanese 1L', 'Blackpepper Sauce 1L',
            'Biji Wijen Sangrai 500g', 'Oregano Kering 250g', 'Parsley Flakes 100g', 'Bubuk Kaldu Ayam 1kg', 'Bubuk Kaldu Sapi 1kg',
            'Bubuk Paprika Smoked 250g', 'Lada Hitam Crush 500g', 'Lada Putih Bubuk 500g', 'Vetsin Penyedap Rasa 1kg', 'Cuka Dapur 600ml'
        ];

        $items = [];
        foreach ($fnbItemsList as $idx => $itemName) {
            $cat = $categories[$idx % count($categories)];
            $brd = $brands[$idx % count($brands)];
            $uom = $uoms[$idx % count($uoms)];

            $sku = 'FNB-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $barcodeVal = '899'.str_pad($idx + 1000000, 10, '0', STR_PAD_LEFT);

            $item = Item::create([
                'category_id' => $cat->id,
                'brand_id' => $brd->id,
                'uom_id' => $uom->id,
                'sku' => $sku,
                'barcode' => $barcodeVal,
                'name' => $itemName,
                'description' => 'Bahan baku berkualitas tinggi untuk operasional resto F&B.',
                'cost_price' => rand(15000, 150000),
                'selling_price' => rand(20000, 220000),
                'min_stock' => rand(10, 20),
                'max_stock' => rand(200, 500),
                'reorder_point' => rand(15, 30),
                'is_active' => true,
            ]);
            $items[] = $item;

            // Seed Barcode record
            Barcode::create([
                'item_id' => $item->id,
                'barcode' => $barcodeVal,
                'barcode_type' => 'EAN13',
                'is_primary' => true
            ]);

            // Seed Stock Summary & Ledger for ALL items (Guaranteed stock for 100 items)
            foreach ($warehouses as $wIdx => $wh) {
                $bin = $bins[($idx + $wIdx) % count($bins)];
                $qty = rand(50, 250);

                StockSummary::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $wh->id,
                    'bin_id' => $bin->id,
                    'quantity' => $qty,
                    'reserved_qty' => rand(0, 10),
                    'allocated_qty' => rand(0, 5),
                ]);

                // Ledger history entry
                StockMovement::create([
                    'reference_number' => 'INIT-STOCK-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                    'transaction_type' => 'stock_in',
                    'item_id' => $item->id,
                    'warehouse_id' => $wh->id,
                    'bin_id' => $bin->id,
                    'quantity' => $qty,
                    'unit_cost' => $item->cost_price,
                    'total_cost' => $qty * $item->cost_price,
                    'notes' => 'Stok awal sistem F&B ERP',
                    'created_by' => 'Inventory System',
                ]);
            }
        }

        // 6. Stock In Demo (20 Transactions)
        for ($i = 1; $i <= 20; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $stIn = StockIn::create([
                'number' => 'STIN-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'supplier_name' => 'PT Supplier F&B Utama ' . $i,
                'warehouse_id' => $wh->id,
                'status' => ($i % 2 == 0) ? 'approved' : 'draft',
                'total_amount' => rand(500000, 3000000),
                'notes' => 'Penerimaan bahan baku mingguan ke ' . $wh->name,
                'approved_by' => ($i % 2 == 0) ? 'Budi Manager' : null,
            ]);

            for ($j = 0; $j < 3; $j++) {
                $itm = $items[($i + $j * 5) % count($items)];
                StockInLine::create([
                    'stock_in_id' => $stIn->id,
                    'item_id' => $itm->id,
                    'bin_id' => $bins[$i % count($bins)]->id,
                    'quantity' => rand(10, 50),
                    'unit_price' => $itm->cost_price,
                    'total_price' => rand(10, 50) * $itm->cost_price,
                    'notes' => 'Kondisi kemasan baik'
                ]);
            }
        }

        // 7. Stock Out Demo (20 Transactions)
        for ($i = 1; $i <= 20; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $stOut = StockOut::create([
                'number' => 'STOUT-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'recipient_name' => 'Dapur Outlet Resto Branch ' . ($i % 5 + 1),
                'warehouse_id' => $wh->id,
                'status' => ($i % 2 == 0) ? 'approved' : 'draft',
                'total_amount' => rand(300000, 2000000),
                'notes' => 'Pengeluaran bahan masak harian',
                'approved_by' => ($i % 2 == 0) ? 'Siti Warehouse' : null,
            ]);

            for ($j = 0; $j < 3; $j++) {
                $itm = $items[($i + $j * 3) % count($items)];
                StockOutLine::create([
                    'stock_out_id' => $stOut->id,
                    'item_id' => $itm->id,
                    'bin_id' => $bins[$i % count($bins)]->id,
                    'quantity' => rand(5, 20),
                    'unit_price' => $itm->selling_price,
                    'total_price' => rand(5, 20) * $itm->selling_price,
                    'notes' => 'Permintaan kitchen'
                ]);
            }
        }

        // 8. Transfer Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $srcWh = $warehouses[($i - 1) % count($warehouses)];
            $dstWh = $warehouses[$i % count($warehouses)];
            $tf = Transfer::create([
                'number' => 'TRF-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'source_warehouse_id' => $srcWh->id,
                'destination_warehouse_id' => $dstWh->id,
                'status' => ($i % 2 == 0) ? 'approved' : 'draft',
                'notes' => 'Transfer antar gudang penyeimbang stok',
                'approved_by' => ($i % 2 == 0) ? 'Logistics Supervisor' : null,
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 7) % count($items)];
                TransferLine::create([
                    'transfer_id' => $tf->id,
                    'item_id' => $itm->id,
                    'source_bin_id' => $bins[0]->id,
                    'destination_bin_id' => $bins[1]->id,
                    'quantity' => rand(10, 30),
                    'notes' => 'Transfer via armada box'
                ]);
            }
        }

        // 9. Adjustment Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $adj = Adjustment::create([
                'number' => 'ADJ-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'warehouse_id' => $wh->id,
                'type' => ($i % 2 == 0) ? 'addition' : 'reduction',
                'status' => ($i % 2 == 0) ? 'approved' : 'draft',
                'notes' => 'Penyesuaian selisih fisik opname',
                'approved_by' => ($i % 2 == 0) ? 'Audit Manager' : null,
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 4) % count($items)];
                AdjustmentLine::create([
                    'adjustment_id' => $adj->id,
                    'item_id' => $itm->id,
                    'bin_id' => $bins[$i % count($bins)]->id,
                    'system_qty' => 100,
                    'actual_qty' => 95,
                    'adjustment_qty' => -5,
                    'reason' => 'Kerusakan fisik kemasan'
                ]);
            }
        }

        // 10. Cycle Count Demo (5 Transactions)
        for ($i = 1; $i <= 5; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $cc = CycleCount::create([
                'number' => 'CC-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'warehouse_id' => $wh->id,
                'status' => 'completed',
                'notes' => 'Stock opname rutin bulanan',
                'conducted_by' => 'Tim Inventory Audit',
            ]);

            for ($j = 0; $j < 3; $j++) {
                $itm = $items[($i + $j * 10) % count($items)];
                CycleCountLine::create([
                    'cycle_count_id' => $cc->id,
                    'item_id' => $itm->id,
                    'bin_id' => $bins[$i % count($bins)]->id,
                    'expected_qty' => 150,
                    'counted_qty' => 150,
                    'variance' => 0,
                    'notes' => 'Sesuai dengan sistem'
                ]);
            }
        }

        // 11. Reservation Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $res = Reservation::create([
                'number' => 'RES-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'customer_name' => 'Catering Event Pelanggan ' . $i,
                'warehouse_id' => $wh->id,
                'status' => ($i % 2 == 0) ? 'reserved' : 'draft',
                'notes' => 'Reservasi bahan untuk pesta pernikahan',
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 8) % count($items)];
                ReservationLine::create([
                    'reservation_id' => $res->id,
                    'item_id' => $itm->id,
                    'quantity' => rand(15, 40),
                    'notes' => 'Hold stok sampai H-1'
                ]);
            }
        }

        // 12. Picking Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $wh = $warehouses[$i % count($warehouses)];
            $pic = Picking::create([
                'number' => 'PIC-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'warehouse_id' => $wh->id,
                'picker_name' => 'Staff Picker ' . $i,
                'status' => 'completed',
                'notes' => 'Pengambilan pesanan catering',
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 6) % count($items)];
                PickingLine::create([
                    'picking_id' => $pic->id,
                    'item_id' => $itm->id,
                    'bin_id' => $bins[$i % count($bins)]->id,
                    'requested_qty' => 20,
                    'picked_qty' => 20
                ]);
            }
        }

        // 13. Packing Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $pac = Packing::create([
                'number' => 'PAK-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'packer_name' => 'Staff Packer ' . $i,
                'status' => 'packed',
                'notes' => 'Pengemasan kardus segel plastik',
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 6) % count($items)];
                PackingLine::create([
                    'packing_id' => $pac->id,
                    'item_id' => $itm->id,
                    'packed_qty' => 20,
                    'box_number' => 'BOX-FNB-00' . $i
                ]);
            }
        }

        // 14. Delivery Demo (10 Transactions)
        for ($i = 1; $i <= 10; $i++) {
            $del = Delivery::create([
                'number' => 'DEL-202608-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime("-$i days")),
                'courier_name' => 'Armada Suba Logistik ' . ($i % 3 + 1),
                'tracking_number' => 'SUBALOG-' . rand(100000, 999999),
                'status' => ($i % 2 == 0) ? 'delivered' : 'shipped',
                'delivery_address' => 'Jl. Boulevard Utama No. ' . ($i * 10) . ', Jakarta',
                'notes' => 'Pengiriman tepat waktu',
            ]);

            for ($j = 0; $j < 2; $j++) {
                $itm = $items[($i + $j * 6) % count($items)];
                DeliveryLine::create([
                    'delivery_id' => $del->id,
                    'item_id' => $itm->id,
                    'delivered_qty' => 20
                ]);
            }
        }

        // 15. Serial Numbers & Batch Numbers
        foreach ($items as $idx => $itm) {
            if ($idx < 20) {
                SerialNumber::create([
                    'item_id' => $itm->id,
                    'warehouse_id' => $warehouses[0]->id,
                    'serial_number' => 'SN-FNB-' . strtoupper(substr(md5($itm->id), 0, 8)),
                    'status' => 'available'
                ]);

                BatchNumber::create([
                    'item_id' => $itm->id,
                    'warehouse_id' => $warehouses[0]->id,
                    'batch_number' => 'BATCH-2026-0' . ($idx % 9 + 1),
                    'manufacture_date' => '2026-01-01',
                    'expiry_date' => '2027-01-01',
                    'quantity' => 100
                ]);
            }
        }

        // 16. Inventory Settings
        $settings = [
            ['setting_key' => 'enable_negative_stock', 'setting_value' => 'false', 'description' => 'Izinkan stok minus pada pengeluaran barang'],
            ['setting_key' => 'default_costing_method', 'setting_value' => 'FIFO', 'description' => 'Metode kalkulasi HPP stok (FIFO, LIFO, Average)'],
            ['setting_key' => 'auto_generate_sku', 'setting_value' => 'true', 'description' => 'Otomatis buat SKU item baru'],
            ['setting_key' => 'low_stock_notification', 'setting_value' => 'true', 'description' => 'Kirim peringatan jika stok mencapai reorder point'],
        ];

        foreach ($settings as $st) {
            InventorySetting::create($st);
        }
    }
}
