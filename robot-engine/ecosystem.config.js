/**
 * =============================================
 * PM2 Ecosystem Configuration
 * ZYN Trade Robot Engine - Production
 * =============================================
 *
 * Commands:
 *   pm2 start ecosystem.config.js                    - Start all apps
 *   pm2 start ecosystem.config.js --only zyn-api     - Start API only
 *   pm2 stop all                                     - Stop all apps
 *   pm2 restart zyn-api                              - Restart API
 *   pm2 logs zyn-api                                 - View logs
 *   pm2 monit                                        - Monitor all
 *   pm2 save                                         - Save process list
 *   pm2 startup                                      - Setup auto-start
 *   pm2 delete all                                   - Remove all apps
 */

const path = require('path');

module.exports = {
  apps: [
    // =========================================
    // Production API Server (Main)
    // =========================================
    {
      name: 'zyn-api',
      script: 'src/api/productionServer.js',
      cwd: __dirname,
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '2G',

      // Environment
      env: {
        NODE_ENV: 'production',
        TZ: 'Asia/Jakarta',
        API_PORT: 3001
      },

      // Logging
      error_file: './logs/api-error.log',
      out_file: './logs/api-out.log',
      log_file: './logs/api-combined.log',
      time: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,

      // Restart policies
      exp_backoff_restart_delay: 1000,
      max_restarts: 10,
      min_uptime: '30s',
      restart_delay: 5000,

      // Graceful shutdown
      kill_timeout: 30000,
      wait_ready: true,
      listen_timeout: 30000,

      // Health check
      health_check_interval: 30000,
      health_check_grace_period: 30000
    },

    // =========================================
    // Development API Server (Optional)
    // =========================================
    {
      name: 'zyn-api-dev',
      script: 'src/api/server.js',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: ['src'],
      watch_delay: 1000,
      ignore_watch: ['node_modules', 'logs', 'sessions', 'data'],

      env: {
        NODE_ENV: 'development',
        TZ: 'Asia/Jakarta',
        API_PORT: 3002
      },

      error_file: './logs/dev-error.log',
      out_file: './logs/dev-out.log',
      time: true
    },

    // =========================================
    // Standalone Robot (Single Trader - Legacy)
    // =========================================
    {
      name: 'zyn-robot',
      script: 'src/index.js',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',

      env: {
        NODE_ENV: 'production',
        TZ: 'Asia/Jakarta'
      },

      error_file: './logs/robot-error.log',
      out_file: './logs/robot-out.log',
      time: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',

      // Restart policies
      exp_backoff_restart_delay: 100,
      max_restarts: 10,
      min_uptime: '10s',

      // Daily restart at 4 AM WIB (21:00 UTC)
      cron_restart: '0 21 * * *'
    },

    // =========================================
    // Auto Trade Scheduler
    // =========================================
    {
      name: 'zyn-scheduler',
      script: 'src/scheduler/autoTradeScheduler.js',
      cwd: __dirname,
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',

      env: {
        NODE_ENV: 'production',
        TZ: 'Asia/Jakarta',
        SCHEDULER_POLL_INTERVAL: 5000,
        SCHEDULER_BATCH_SIZE: 50,
        SCHEDULER_MAX_CONCURRENT: 20
      },

      error_file: './logs/scheduler-error.log',
      out_file: './logs/scheduler-out.log',
      time: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',

      // Restart policies
      exp_backoff_restart_delay: 1000,
      max_restarts: 10,
      min_uptime: '30s'
    },

    // =========================================
    // Queue Cleanup Scheduler
    // =========================================
    {
      name: 'zyn-cleanup',
      script: 'src/scripts/cleanup.js',
      cwd: __dirname,
      instances: 1,
      autorestart: false,
      watch: false,

      // Run every hour
      cron_restart: '0 * * * *',

      env: {
        NODE_ENV: 'production'
      },

      error_file: './logs/cleanup-error.log',
      out_file: './logs/cleanup-out.log'
    }
  ],

  // =========================================
  // Deployment Configuration
  // =========================================
  deploy: {
    production: {
      user: 'root',
      host: 'your-vps-ip',
      ref: 'origin/main',
      repo: 'git@github.com:your-username/PLAN-ROBOT-1.git',
      path: '/root/PLAN-ROBOT-1',
      'pre-deploy-local': '',
      'post-deploy': 'cd robot-engine && npm install && pm2 reload ecosystem.config.js --env production',
      'pre-setup': ''
    }
  }
};
