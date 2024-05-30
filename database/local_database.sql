-- row query function GetTransactions in TransactionsController
SELECT t.amount, t.description, t.type, t.transaction_date, c.name AS category_name
FROM transactions t
    JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = 1 AND t.type = 'expense'
    ORDER BY t.created_at DESC
    LIMIT 10
    OFFSET 10;
