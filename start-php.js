const { spawn } = require('child_process');
const path = require('path');

function startPHPServer() {
  const phpPath = 'php'; // or provide full path if needed
  const appPath = path.join(__dirname, 'php');
  const port = 8000;

  const server = spawn(phpPath, ['-S', `172.16.80.137:${port}`, '-t', appPath]);

  server.stdout.on('data', data => console.log(`PHP: ${data}`));
  server.stderr.on('data', data => console.error(`PHP Error: ${data}`));

  process.on('exit', () => server.kill());

  return `http://172.16.80.137:${port}`;
}

module.exports = startPHPServer;
