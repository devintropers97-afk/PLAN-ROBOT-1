/**
 * PM2 Ecosystem Configuration
 * ZYN Trade Robot Engine
 *
 * Commands:
 *   pm2 start ecosystem.config.js    - Start the robot
 *   pm2 stop zyn-robot               - Stop the robot
 *   pm2 restart zyn-robot            - Restart the robot
 *   pm2 logs zyn-robot               - View logs
 *   pm2 monit                        - Monitor all processes
 *   pm2 save                         - Save current process list
 *   pm2 startup                      - Setup auto-start on boot
 */

module.exports = {
  apps: [
    {
      name: 'zyn-robot',
      script: 'src/index.js',
      cwd: '/home/zyn-robot',  // Change this to your VPS path
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        TZ: 'Asia/Jakarta'
      },
      env_production: {
        NODE_ENV: 'production',
        TZ: 'Asia/Jakarta'
      },
      error_file: './logs/pm2-error.log',
      out_file: './logs/pm2-out.log',
      log_file: './logs/pm2-combined.log',
      time: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,

      // Restart policies
      exp_backoff_restart_delay: 100,
      max_restarts: 10,
      min_uptime: '10s',

      // Graceful shutdown
      kill_timeout: 5000,
      wait_ready: true,
      listen_timeout: 10000,

      // Cron restart every day at 4 AM WIB (21:00 UTC previous day)
      cron_restart: '0 21 * * *'
    }
  ]
};
