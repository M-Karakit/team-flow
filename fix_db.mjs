import mysql from 'mysql2/promise';

async function fixMySQL() {
    console.log(">> Attempting to fix MySQL caching_sha2_password authentication plugin issue...");

    // Get DB credentials from environment variables injected by Railway
    const host = process.env.DB_HOST || process.env.MYSQL_HOST;
    const user = process.env.DB_USERNAME || process.env.MYSQL_USER || 'root';
    const password = process.env.DB_PASSWORD || process.env.MYSQL_PASSWORD || '';
    const port = parseInt(process.env.DB_PORT || process.env.MYSQL_PORT || '3306', 10);
    const database = process.env.DB_DATABASE || process.env.MYSQL_DATABASE;

    if (!host || !password) {
        console.log(">> Missing DB_HOST or DB_PASSWORD. Cannot run fix_db script.");
        return;
    }

    try {
        console.log(`Connecting to ${host}:${port} as ${user}...`);
        
        // Connect to MySQL
        const connection = await mysql.createConnection({
            host: host,
            user: user,
            password: password,
            port: port,
            database: database
        });

        console.log(">> Connected successfully securely using caching_sha2_password (via mysql2)!");

        // Execute the ALTER USER query to revert to mysql_native_password
        const sql = `ALTER USER '${user}'@'%' IDENTIFIED WITH mysql_native_password BY '${password}';`;
        console.log(`>> Executing ALTER USER for '${user}'...`);
        
        await connection.query(sql);
        console.log(">> Password authentication plugin successfully changed to mysql_native_password!");

        await connection.query("FLUSH PRIVILEGES;");
        console.log(">> Privileges flushed.");

        await connection.end();
        console.log(">> Database fix complete. The PHP deployment will now work correctly.");
    } catch (err) {
        console.error(">> Error fixing database authentication:");
        console.error(err);
    }
}

fixMySQL();
