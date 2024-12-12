-- User Table
CREATE TABLE User (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    SSN VARCHAR(11) UNIQUE NOT NULL CHECK (LENGTH(SSN) = 9 AND SSN REGEXP '^[0-9]+$'),
    Name VARCHAR(100),
    UserPhoneNo VARCHAR(10) CHECK (LENGTH(UserPhoneNo) = 10 AND UserPhoneNo REGEXP '^[0-9]+$'),
    UserAccountID INT,
    UserWalletID INT
);

-- PhoneNo Table
CREATE TABLE PhoneNo (
    PhoneNo VARCHAR(15) PRIMARY KEY CHECK (LENGTH(PhoneNo) = 10 AND PhoneNo REGEXP '^[0-9]+$'),
    Verifies INT,
    FOREIGN KEY (Verifies) REFERENCES User(UserID)
);

-- EmailAddress Table
CREATE TABLE EmailAddress (
    EmailID VARCHAR(100) PRIMARY KEY,
    EUserID INT,
    FOREIGN KEY (EUserID) REFERENCES User(UserID)
);

-- BankAccount Table
CREATE TABLE BankAccount (
    AccountID INT AUTO_INCREMENT PRIMARY KEY,
    AccountNumber VARCHAR(20) UNIQUE NOT NULL,
    BankID INT UNIQUE NOT NULL
);

-- Wallet Table
CREATE TABLE Wallet (
    WalletID INT AUTO_INCREMENT PRIMARY KEY,
    Balance DECIMAL(10, 2)
);

-- Transaction Table
CREATE TABLE Transaction (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY,
    TransactionType ENUM('Debit', 'Credit'),
    Amount DECIMAL(10, 2) NOT NULL,
    Memo TEXT,
    InitializedTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    RecipientID INT,
    Status ENUM('Pending', 'Completed', 'Cancelled'),
    TWalletID INT,
    FOREIGN KEY (RecipientID) REFERENCES User(UserID),
    FOREIGN KEY (TWalletID) REFERENCES Wallet(WalletID)
);