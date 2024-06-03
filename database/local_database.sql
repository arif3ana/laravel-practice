-- row query function GetTransactions in TransactionsController
SELECT t.transaction_name, t.amount, t.description, t.type, t.transaction_date, c.name AS category_name
FROM transactions t
    JOIN categories c ON t.category_id = c.id
WHERE
    t.user_id = 1
    AND t.type = 'income'
ORDER BY t.created_at DESC
LIMIT 10
OFFSET
    10;

-- row query function ShowCreateTransaction in TransactionsController
SELECT id, name FROM categories WHERE user_id = 1;

-- row query function CreateTransaction in TransactionsController
INSERT INTO
    transactions (
        user_id,
        amount,
        description,
        type,
        category_id,
        transaction_date
    )
VALUES (
        1,
        100,
        'Test',
        'income',
        1,
        '2021-01-01'
    );

-- row query function ShowUpdateTransaction in TransactionsController
SELECT t.id, t.transaction_name, t.transaction_date, t.amount, t.type, t.description, c.name as category_name
FROM transactions t
    join categories c on c.id = t.category_id
WHERE
    t.id = 1;


Select * from categories;
