# README: SQL Commands for Table Creation

This document contains the SQL commands required to create all the necessary tables for the system. The tables are designed to manage orders, menus, employees, and table statuses for a restaurant management application.

---

## **Table 1: `tab_menu`**
Stores information about the menu items, including their options and status.

```sql
CREATE TABLE `tab_menu` (
    `menu_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `menu_name` VARCHAR(255) NOT NULL,
    `menu_type` VARCHAR(100) NOT NULL, -- Example: ก๋วยเตี๋ยว, ข้าว, น้ำ
    `menu_price` DECIMAL(10, 2) NOT NULL,
    `menu_description` TEXT,
    `menu_pic` VARCHAR(255),
    `menu_sales` INT(11) DEFAULT 0, -- Sales count
    `menu_se` JSON, -- Stores menu options as JSON
    `menu_status` TINYINT(1) DEFAULT 1, -- 1 = Active, 0 = Inactive
    `timestmp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## **Table 2: `tab_order`**
Stores information about orders, including their table number and status.

```sql
CREATE TABLE `tab_order` (
    `order_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `table_number` INT(11) NOT NULL,
    `order_status` TINYINT(1) DEFAULT 0, -- 0 = Pending, 1 = Confirmed, 2 = Paid
    `order_total_price` DECIMAL(10, 2) DEFAULT 0.00,
    `order_payment_type` VARCHAR(50), -- Example: cash, credit
    `timestmp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## **Table 3: `tab_order_details`**
Stores details of each item in an order.

```sql
CREATE TABLE `tab_order_details` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT(11) NOT NULL,
    `menu_id` INT(11) NOT NULL,
    `menu_se` JSON, -- Stores selected options as JSON
    `quantity` INT(11) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `substatus` TINYINT(1) DEFAULT 0, -- 0 = Pending, 1 = Confirmed, 2 = Cancelled, 3 = Out of stock
    `timestmp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `tab_order`(`order_id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_id`) REFERENCES `tab_menu`(`menu_id`)
);
```

---

## **Table 4: `tab_table`**
Stores information about table statuses in the restaurant.

```sql
CREATE TABLE `tab_table` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `table_number` INT(11) NOT NULL UNIQUE,
    `table_status` TINYINT(1) DEFAULT 0 -- 0 = Available, 1 = Occupied
);
```

---

## **Table 5: `tab_employee`**
Stores information about employees.

```sql
CREATE TABLE `tab_employee` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `status` TINYINT(1) DEFAULT 1, -- 1 = Active, 0 = Inactive
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` TINYINT(1) NOT NULL -- 1 = Waiter, 2 = Chef
);
```

---

## **Table 6: `tab_manager`**
Stores manager credentials for administrative purposes.

```sql
CREATE TABLE `tab_manager` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
);
```

---

### **Notes**
1. **`menu_se` and `menu_se` in JSON Columns**:
   - Use JSON to store menu options (e.g., {"Size": {"Small": 0, "Large": 10}}).
2. **Foreign Key Constraints**:
   - Relationships between `tab_order` and `tab_order_details`, as well as between `tab_menu` and `tab_order_details`, are enforced via foreign keys.
3. **Default Values**:
   - `order_status`, `substatus`, and other status fields have default values for smoother operation.

---

### **Order of Creation**
1. `tab_menu`
2. `tab_order`
3. `tab_order_details`
4. `tab_table`
5. `tab_employee`
6. `tab_manager`

---

For further modifications or additional features, adjust the tables accordingly.
