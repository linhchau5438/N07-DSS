-- MySQL/MariaDB: run after php artisan migrate.
-- Read-only examples for the schema implemented in this repository.

-- Daily consumption. Zero days are included by the configured observation policy.
SELECT product_id, demand_date, consumed_qty FROM v_daily_demand
ORDER BY product_id, demand_date;

-- MA across complete days, using a configurable window per product.
SELECT s.product_id, s.ma_days,
       COALESCE(SUM(d.consumed_qty), 0) / s.ma_days AS demand_per_day
FROM product_reorder_settings s
LEFT JOIN v_daily_demand d ON d.product_id = s.product_id
 AND d.demand_date >= DATE_SUB(CURRENT_DATE, INTERVAL s.ma_days DAY)
 AND d.demand_date < CURRENT_DATE
GROUP BY s.product_id, s.ma_days;

-- The latest persisted decision snapshot. Refresh DSS in the app before inspection.
SELECT p.product_code, r.on_hand, r.on_order, r.inventory_position,
       r.average_daily_demand, r.safety_stock, r.reorder_point,
       r.suggested_quantity, r.stock_status, r.created_at
FROM v_reorder r JOIN products p ON p.id = r.product_id
WHERE p.status = 1 ORDER BY p.product_code;

-- Verify the document's numerical example in SQL: ROP 72, Q 93.
SELECT 13 * 4 + 20 AS reorder_point,
       CASE WHEN 70 <= 13 * 4 + 20
            THEN CEIL(GREATEST(0, 13 * (4 + 7) + 20 - 70) / 1) * 1
            ELSE 0 END AS suggested_quantity;

-- OLAP roll-up. Group units separately to avoid combining boxes with bottles.
SELECT YEAR(f.transaction_date) AS year_no, MONTH(f.transaction_date) AS month_no,
       f.category_id, f.unit,
       SUM(CASE WHEN f.type = 'import' THEN f.quantity ELSE 0 END) AS imported,
       SUM(CASE WHEN f.type = 'export' THEN f.quantity ELSE 0 END) AS exported
FROM v_inventory_facts f
GROUP BY YEAR(f.transaction_date), MONTH(f.transaction_date), f.category_id, f.unit WITH ROLLUP;

-- Pivot purposes, with a date slice. Supply actual reporting dates when needed.
SELECT p.product_code, p.unit,
       SUM(CASE WHEN f.type = 'import' THEN f.quantity ELSE 0 END) AS imported,
       SUM(CASE WHEN f.type = 'export' AND f.issue_purpose = 'sale' THEN f.quantity ELSE 0 END) AS consumed,
       SUM(CASE WHEN f.type = 'export' AND f.issue_purpose <> 'sale' THEN f.quantity ELSE 0 END) AS other_exports
FROM v_inventory_facts f JOIN products p ON p.id = f.product_id
WHERE f.transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY p.id, p.product_code, p.unit;
