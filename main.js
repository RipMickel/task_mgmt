const { app, BrowserWindow } = require('electron');
const startPHPServer = require('./start-php');

let mainWindow;

function createWindow() {
  const serverURL = startPHPServer();

  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: { nodeIntegration: false }
  });

  mainWindow.loadURL(serverURL);
  mainWindow.on('closed', () => { mainWindow = null; });
}

app.whenReady().then(createWindow);
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
