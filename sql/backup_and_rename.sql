# Step 1: Backup the database using mysqldump
mysqldump -u root -p your_database_name > backup.sql

# Step 2–4: Copy table, rename it, and drop original — all in SQL
mysql -u root -p your_database_name -e "
  CREATE TABLE users_backup AS SELECT * FROM users;
  RENAME TABLE users_backup TO ducks;
  DROP TABLE users;
"
