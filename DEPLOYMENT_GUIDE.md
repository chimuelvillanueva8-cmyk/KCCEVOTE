# Cloud Deployment Guide: Vercel vs Render / Docker

This project (KEVS) is a **CodeIgniter 4 (PHP 8.2+)** application requiring a **MySQL** database.

Below are the two best free deployment options:
1. **Render.com (Recommended for full PHP + MySQL apps)**: Full Apache container, persistent sessions, and file uploads.
2. **Vercel (Fast Serverless)**: Global edge compute, serverless PHP execution.

---

## Prerequisites: Free Cloud MySQL Database (TiDB Cloud)
Because neither Vercel nor Render free tier includes a built-in MySQL database server, you need a free cloud MySQL database. **TiDB Cloud Serverless** is 100% free forever (5GB storage, SSL enabled, 0 maintenance).

1. Sign up for free at [tidbcloud.com](https://tidbcloud.com/).
2. Click **Create Cluster** -> choose **Serverless (Free)**.
3. Choose AWS region (e.g. `ap-southeast-1` Singapore or nearest to you).
4. In the cluster console, click **Connect**:
   - Note the **Host**, **Port** (usually `4000`), **User**, and **Password**.
5. Click **SQL Editor** in the TiDB Cloud dashboard and run the contents of [`db/voting_system_schema.sql`](./db/voting_system_schema.sql) to create all tables.
6. Create your initial admin account:
   ```sql
   INSERT INTO `admins` (`admin_id_number`, `is_builtin_admin`, `username`, `password`, `full_name`, `created_at`) 
   VALUES (
       'ADMIN-001',
       1,
       'admin',
       '$2y$10$6Kf3b7KmDuwJqJTB3TWAL.wYQOIUHI9yfAAjhTt.Yon9YTkOiu3sK',
       'System Administrator',
       NOW()
   );
   ```
   *(Credentials: Username `admin`, Password `Admin@123`)*.

---

## OPTION 1: Deploy to Vercel (Serverless)

We have configured `vercel.json`, `api/index.php`, and `app/Config/Paths.php` to handle Vercel's serverless environment.

### Steps:
1. Push your repository to **GitHub**.
2. Go to [vercel.com](https://vercel.com) and sign in.
3. Click **Add New...** -> **Project**.
4. Import your GitHub repository.
5. In the **Environment Variables** section, add:
   | Key | Value |
   | --- | --- |
   | `CI_ENVIRONMENT` | `production` |
   | `app.baseURL` | `https://your-app-name.vercel.app/` *(trailing slash required)* |
   | `database.default.hostname` | *(Your TiDB Cloud Host, e.g. gateway01...)* |
   | `database.default.database` | `voting_system` |
   | `database.default.username` | *(Your TiDB Cloud User)* |
   | `database.default.password` | *(Your TiDB Cloud Password)* |
   | `database.default.port` | `4000` |
   | `database.default.DBDriver` | `MySQLi` |
6. Click **Deploy**.

> [!NOTE]
> **Vercel Limitations:** Vercel functions are stateless and read-only (except `/tmp`). Temporary logs and cache are redirected to `/tmp/writable`. Any files uploaded dynamically (e.g., candidate photos uploaded after deployment) will be lost on container cold starts.

---

## OPTION 2: Deploy to Render.com (Recommended Best Choice)

Render runs the full application inside a real Apache + PHP 8.2 container using the included [`Dockerfile`](./Dockerfile).

### Steps:
1. Push your repository to **GitHub**.
2. Go to [render.com](https://render.com) and sign in.
3. Click **New +** -> **Web Service**.
4. Connect your GitHub repository.
5. Render will automatically detect the **Docker** runtime.
6. Set:
   - **Environment**: `Docker`
   - **Region**: Singapore or Oregon (choose closest to your database)
   - **Instance Type**: `Free`
7. Under **Environment Variables**, add:
   | Key | Value |
   | --- | --- |
   | `CI_ENVIRONMENT` | `production` |
   | `app.baseURL` | `https://your-service.onrender.com/` |
   | `database.default.hostname` | *(Your TiDB Cloud Host)* |
   | `database.default.database` | `voting_system` |
   | `database.default.username` | *(Your TiDB Cloud User)* |
   | `database.default.password` | *(Your TiDB Cloud Password)* |
   | `database.default.port` | `4000` |
   | `database.default.DBDriver` | `MySQLi` |
8. Click **Create Web Service**. Render will build and deploy the container.

