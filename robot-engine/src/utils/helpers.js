/**
 * ZYN Trade Robot - Helper Functions
 */

const crypto = require('crypto');

// MUST match PHP ENCRYPTION_KEY in includes/functions.php
const ENCRYPTION_KEY = 'ZYN_TR4D3_S3CR3T_K3Y_2024!@#$';

/**
 * Decrypt password encrypted by PHP
 * Matches PHP decryptPassword() function exactly
 */
function decryptPassword(encryptedPassword) {
    if (!encryptedPassword) return '';

    try {
        // Create key using SHA256 hash (same as PHP)
        const key = crypto.createHash('sha256').update(ENCRYPTION_KEY).digest();

        // Decode base64
        const data = Buffer.from(encryptedPassword, 'base64');

        // Extract IV (first 16 bytes)
        const iv = data.slice(0, 16);

        // Extract encrypted data (rest is base64 encoded ciphertext from PHP)
        const encryptedBase64 = data.slice(16).toString('utf8');
        const encrypted = Buffer.from(encryptedBase64, 'base64');

        // Decrypt using AES-256-CBC
        const decipher = crypto.createDecipheriv('aes-256-cbc', key, iv);
        let decrypted = decipher.update(encrypted);
        decrypted = Buffer.concat([decrypted, decipher.final()]);

        return decrypted.toString('utf8');
    } catch (error) {
        console.error('Decryption error:', error.message);
        return '';
    }
}

/**
 * Encrypt password (for testing, matches PHP encryptPassword)
 */
function encryptPassword(password) {
    const key = crypto.createHash('sha256').update(ENCRYPTION_KEY).digest();
    const iv = crypto.randomBytes(16);

    const cipher = crypto.createCipheriv('aes-256-cbc', key, iv);
    let encrypted = cipher.update(password, 'utf8', 'base64');
    encrypted += cipher.final('base64');

    // Combine IV + encrypted (as base64 string) and encode whole thing
    const combined = Buffer.concat([iv, Buffer.from(encrypted, 'utf8')]);
    return combined.toString('base64');
}

/**
 * Check if today is weekend (Saturday or Sunday)
 */
function isWeekend() {
    const day = new Date().getDay();
    return day === 0 || day === 6; // 0 = Sunday, 6 = Saturday
}

/**
 * Check if current time is within trading schedule
 */
function isWithinSchedule(scheduleMode, settings = {}) {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes(); // Minutes since midnight

    switch (scheduleMode) {
        case 'auto_24h':
            return true;

        case 'best_hours':
            // 14:00 - 22:00 WIB (London & NY session overlap)
            const bestStart = 14 * 60; // 14:00
            const bestEnd = 22 * 60;   // 22:00
            return currentTime >= bestStart && currentTime <= bestEnd;

        case 'custom_single':
            if (settings.schedule_start_time && settings.schedule_end_time) {
                const start = timeToMinutes(settings.schedule_start_time);
                const end = timeToMinutes(settings.schedule_end_time);
                return currentTime >= start && currentTime <= end;
            }
            return false;

        case 'multi_session':
            if (settings.schedule_sessions) {
                const sessions = JSON.parse(settings.schedule_sessions || '[]');
                for (const session of sessions) {
                    const start = timeToMinutes(session.start);
                    const end = timeToMinutes(session.end);
                    if (currentTime >= start && currentTime <= end) {
                        return true;
                    }
                }
            }
            return false;

        case 'per_day':
            if (settings.schedule_per_day) {
                const perDay = JSON.parse(settings.schedule_per_day || '{}');
                const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const today = dayNames[now.getDay()];

                if (perDay[today] && perDay[today].enabled) {
                    const start = timeToMinutes(perDay[today].start);
                    const end = timeToMinutes(perDay[today].end);
                    return currentTime >= start && currentTime <= end;
                }
            }
            return false;

        default:
            return false;
    }
}

/**
 * Convert time string (HH:MM) to minutes since midnight
 */
function timeToMinutes(timeStr) {
    if (!timeStr) return 0;
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
}

/**
 * Format currency (IDR)
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Sleep for specified milliseconds
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Generate random delay (for human-like behavior)
 */
function randomDelay(min = 500, max = 2000) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Get next trading day
 */
function getNextTradingDay() {
    const date = new Date();
    do {
        date.setDate(date.getDate() + 1);
    } while (date.getDay() === 0 || date.getDay() === 6);

    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

module.exports = {
    decryptPassword,
    encryptPassword,
    isWeekend,
    isWithinSchedule,
    timeToMinutes,
    formatCurrency,
    sleep,
    randomDelay,
    getNextTradingDay
};
