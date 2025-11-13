---

# 🖥️ PHP-Electron App

This project wraps a PHP web application inside an **Electron desktop app**.
It runs a local PHP development server and displays your existing PHP interface in a native desktop window — allowing full offline use and desktop integration.

---

## 🚀 Step 8: Run the Application

Make sure all dependencies are installed:

```bash
npm install
```

Then start your Electron app:

```bash
npm start
```

When executed, Electron will:

1. Launch a local PHP server on **[http://127.0.0.1:8000](http://127.0.0.1:8000)**
2. Load your PHP app inside an Electron window

If your PHP app’s main file is `index.php` inside the `/php` directory, it will open automatically.

---

## 🧱 Step 9: Package for Distribution

You can turn your Electron app into a standalone desktop application.

### 1. Install Electron Packager

```bash
npm install electron-packager --save-dev
```

### 2. Build your Application

Run the following command to package your project:

```bash
npx electron-packager . MyPHPApp --platform=win32 --arch=x64 --out=dist
```

* `MyPHPApp` → name of your final app
* `--platform` → target OS (`win32`, `darwin`, or `linux`)
* `--arch` → CPU architecture (`x64`, `arm64`, etc.)
* Output will appear inside the `dist/` folder.

To build for multiple platforms:

```bash
npx electron-packager . MyPHPApp --all --out=dist
```

---

## 📦 Step 10: Run the Packaged App

Navigate to the `dist` folder and open your generated executable (e.g., `MyPHPApp.exe` on Windows).

> ✅ Your PHP application now runs as a native desktop app — no separate web server required!

---

## ⚙️ Step 11: Configuration Notes

### 🔹 PHP Path

If your system’s `php` command is not in PATH, edit **start-php.js**:

```js
const phpPath = "C:\\path\\to\\php.exe";
```

### 🔹 Custom Port

Change the port in `start-php.js`:

```js
const port = 8080;
```

### 🔹 Change Default Page

Modify the Electron `main.js` file:

```js
mainWindow.loadURL(`${serverURL}/dashboard.php`);
```

---

## 🧰 Step 12: Optional Enhancements

### 🌐 1. Bundle PHP Runtime

To make the app fully offline:

* Include the `php` binary inside your app folder (e.g., `/php-bin`)
* Reference it in `start-php.js`

  ```js
  const phpPath = path.join(__dirname, 'php-bin', 'php.exe');
  ```

### 🪟 2. Hide Developer Tools and Menus

In **main.js**, after creating the window:

```js
mainWindow.removeMenu();
mainWindow.setMenuBarVisibility(false);
```

### 💡 3. Add a Splash Screen

You can display a loading screen while PHP starts using a small HTML preloader or by waiting a few seconds before loading the main URL.

### 🗃️ 4. Database Integration

Electron supports local databases:

* SQLite (recommended for offline use)
* MySQL (for LAN or online setups)

You can connect from PHP as usual.

---

## 🧾 Step 13: Folder Structure Overview

```
php-electron/
│
├── main.js                # Electron entry point
├── start-php.js           # Starts the local PHP server
├── package.json
├── php/                   # Your PHP application
│   ├── index.php
│   └── ...
├── dist/                  # Generated installers
└── README.md
```

---

## 🧑‍💻 Step 14: Troubleshooting

| Issue                     | Possible Fix                                  |
| ------------------------- | --------------------------------------------- |
| PHP server doesn’t start  | Check if PHP is in system PATH                |
| White screen in Electron  | Ensure `index.php` is inside `/php` directory |
| Port already in use       | Change `port` in `start-php.js`               |
| Packaged app won’t launch | Run Electron from CLI to check logs           |

---

## 🧩 Step 15: Next Steps

* Integrate **auto-updates** via [electron-updater](https://www.electron.build/auto-update)
* Add an **app icon** (`--icon` option in packaging)
* Implement **native file system dialogs** (for saving/loading data)
* Bundle **SQLite or local JSON storage** for persistent offline data

---

## 🏁 Summary

You now have a fully functioning **Electron + PHP desktop app**!
This approach combines:

* PHP’s mature backend capabilities
* Electron’s desktop integration
* Node.js automation for packaging and deployment

---
