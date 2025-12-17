/**
 * ZYN Trade Robot - Logger Utility
 * Using Winston for structured logging
 */

const winston = require('winston');
const path = require('path');

const logLevel = process.env.LOG_LEVEL || 'info';

const logger = winston.createLogger({
    level: logLevel,
    format: winston.format.combine(
        winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
        winston.format.printf(({ timestamp, level, message, ...meta }) => {
            let metaStr = Object.keys(meta).length ? JSON.stringify(meta) : '';
            return `[${timestamp}] [${level.toUpperCase()}] ${message} ${metaStr}`;
        })
    ),
    transports: [
        // Console output
        new winston.transports.Console({
            format: winston.format.combine(
                winston.format.colorize(),
                winston.format.timestamp({ format: 'HH:mm:ss' }),
                winston.format.printf(({ timestamp, level, message }) => {
                    return `[${timestamp}] ${level}: ${message}`;
                })
            )
        }),
        // File output - All logs
        new winston.transports.File({
            filename: path.join(__dirname, '../../logs/robot.log'),
            maxsize: 5242880, // 5MB
            maxFiles: 5
        }),
        // File output - Errors only
        new winston.transports.File({
            filename: path.join(__dirname, '../../logs/error.log'),
            level: 'error',
            maxsize: 5242880,
            maxFiles: 5
        }),
        // File output - Trades only
        new winston.transports.File({
            filename: path.join(__dirname, '../../logs/trades.log'),
            level: 'info',
            maxsize: 5242880,
            maxFiles: 10
        })
    ]
});

module.exports = logger;
