# Context: ERP LATEST

This rule file serves as the memory for the "ERP LATEST" context. When the user mentions "ERP LATEST", they are referring to the ongoing development and integration of the Suba ERP system.

## Project Architecture & UI/UX Guidelines
- **Framework**: Laravel 11.x, PHP 8.3.
- **Frontend Approach**: Single Page Application (SPA) feel achieved through `display: none/flex` toggling on `<section>` elements inside `resources/views/master-portal.blade.php`.
- **UI/UX Aesthetics**:
  - Baseline Color Palette: #0C3527 (Dark Green/Primary) and #D9EFE9 (Soft Green/Accent).
  - Modern, premium design featuring glassmorphism (`backdrop-filter: blur()`), rounded edges (`border-radius: 20px`), smooth transitions, and shadow effects.
- **Modals**: Global modals must be placed *outside* of the individual `<section class="view-section">` wrappers in the Blade views to ensure they are visible across the entire SPA.

## Recent Integrations (The ERP LATEST Milestone)
- **Single Source of Truth (Products)**: 
  - The Gudang (Inventory) module originally used a legacy `InventoryUmkm` table.
  - It has been fully migrated to use the standard `Product` model, identical to Purchasing and Production.
  - `InventoryUmkmController` was rewritten to interact with `Product`. Legacy direct queries to `InventoryUmkm` in `ProductionController` were removed.
- **Stock Movements**:
  - All stock changes must go through `App\Services\InventoryLedgerService` (`move()` method).
  - The `actual_stock` displayed in Gudang is dynamically calculated using `$inventoryService->balance()`.
- **SPA State Sync**: 
  - Modals in modules (like Purchase Request and Purchase Order in `purchasing.js`) must asynchronously fetch the latest data (`await this.loadProducts()`) every time they are opened to prevent stale data when switching tabs.
- **Data Flow**:
  - **Purchasing -> Gudang**: Creating a Goods Receipt (GR) uses the Ledger Service to add stock, which instantly reflects in Gudang.
  - **Production -> Gudang**: Consuming materials (Issue) deducts stock via the Ledger Service. Completing a Work Order adds finished goods stock.

## Agent Directives
- **Direct Execution Protocol**: The user prefers the agent to execute tasks directly without asking for permission. The command often used is: *"Lakukan kompas, buat plan-nya, dan langsung eksekusi tanpa menunggu persetujuan saya."*
- **Avoid Silos**: Always ensure that adding a feature in one division (e.g., HR, Finance, Inventory) reflects accurately across all other interconnected divisions.

Use this context whenever "ERP LATEST" is invoked to understand the state of the system and user preferences.
