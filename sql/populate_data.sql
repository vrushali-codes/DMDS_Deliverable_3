-- Insert Users
INSERT INTO User (UserID, SSN, Name, UserPhoneNo, UserAccountID, UserWalletID) VALUES 
(1, '123456789', 'Alice Johnson', '1234567890', NULL, NULL),
(2, '234567890', 'Bob Smith', '2345678901', NULL, NULL),
(3, '345678901', 'Charlie Brown', '3456789012', NULL, NULL),
(4, '456789012', 'Diana Prince', '4567890123', NULL, NULL),
(5, '567890123', 'Evan Turner', '5678901234', NULL, NULL),
(6, '678901234', 'Fiona Gallagher', '6789012345', NULL, NULL),
(7, '789012345', 'George Carter', '7890123456', NULL, NULL),
(8, '890123456', 'Hannah Lee', '8901234567', NULL, NULL),
(9, '901234567', 'Ian Somerhalder', '9012345678', NULL, NULL),
(10, '012345678', 'Jane Austen', '0123456789', NULL, NULL);

-- Insert Phone Numbers 
INSERT INTO PhoneNo (PhoneNo, Verifies) VALUES 
('1234567890', 1), 
('2345678901', 2), 
('3456789012', 3), 
('4567890123', 4), 
('5678901234', 5), 
('6789012345', 6), 
('7890123456', 7), 
('8901234567', 8), 
('9012345678', 9), 
('0123456789', 10); 

-- Insert Email Addresses 
INSERT INTO EmailAddress (EmailID, EUserID) VALUES 
('alice@example.com', 1), 
('bob@example.com', 2), 
('charlie@example.com', 3), 
('diana@example.com', 4), 
('evan@example.com', 5), 
('fiona@example.com', 6), 
('george@example.com', 7), 
('hannah@example.com', 8), 
('ian@example.com', 9), 
('jane@example.com', 10);

-- Insert Bank Accounts
INSERT INTO BankAccount (AccountID, AccountNumber, BankID) VALUES 
(1, '1001001001', 1),
(2, '2002002002', 2),
(3, '3003003003', 3),
(4, '4004004004', 4),
(5, '5005005005', 5),
(6, '6006006006', 6),
(7, '7007007007', 7),
(8, '8008008008', 8),
(9, '9009009009', 9),
(10, '0100100100', 10);

-- Insert Wallets
INSERT INTO Wallet (WalletID, Balance) VALUES 
(1, 1000.00),
(2, 2000.00),
(3, 1500.00),
(4, 3000.00),
(5, 1200.00),
(6, 2500.00),
(7, 1100.00),
(8, 1800.00),
(9, 900.00),
(10, 200.00);

-- Insert Transactions
INSERT INTO Transaction (TransactionID, TransactionType, Amount, Memo, RecipientID, Status, TWalletID) VALUES 
(1, 'Credit', 500.00, 'Salary Deposit', 1, 'Completed', 1),
(2, 'Debit', 100.00, 'Grocery Shopping', 2, 'Completed', 2),
(3, 'Credit', 700.00, 'Freelance Work', 3, 'Completed', 3),
(4, 'Debit', 50.00, 'Coffee Shop', 4, 'Completed', 4),
(5, 'Credit', 1200.00, 'Bonus', 5, 'Completed', 5),
(6, 'Debit', 300.00, 'Online Purchase', 6, 'Completed', 6),
(7, 'Credit', 400.00, 'Gift', 7, 'Completed', 7),
(8, 'Debit', 250.00, 'Utility Bill', 8, 'Completed', 8),
(9, 'Credit', 600.00, 'Consulting', 9, 'Completed', 9),
(10, 'Debit', 80.00, 'Dinner', 10, 'Completed', 10);

-- Update User table with BankAccount and Wallet references
UPDATE User u
SET 
    UserAccountID = (SELECT b.AccountID FROM BankAccount b WHERE b.AccountID = u.UserID),
    UserWalletID = (SELECT w.WalletID FROM Wallet w WHERE w.WalletID = u.UserID);

