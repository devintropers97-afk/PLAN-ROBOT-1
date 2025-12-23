/**
 * =============================================
 * ZYN Trade Robot - Cleanup Script
 * =============================================
 *
 * Scheduled cleanup for:
 * - Old completed/failed jobs
 * - Expired sessions
 * - Old log files
 * - Old screenshots
 *
 * Run manually: node src/scripts/cleanup.js
 * Scheduled: PM2 cron every hour
 */

require('dotenv').config();
const fs = require('fs');
const path = require('path');

const LOG_PREFIX = '[Cleanup]';

function log(message) {
    console.log(`${LOG_PREFIX} ${new Date().toISOString()} - ${message}`);
}

/**
 * Clean old files from directory
 */
function cleanOldFiles(directory, maxAgeHours = 24, extensions = []) {
    if (!fs.existsSync(directory)) {
        return 0;
    }

    const now = Date.now();
    const maxAge = maxAgeHours * 60 * 60 * 1000;
    let deleted = 0;

    try {
        const files = fs.readdirSync(directory);

        for (const file of files) {
            // Skip .gitkeep
            if (file === '.gitkeep') continue;

            const filePath = path.join(directory, file);
            const stats = fs.statSync(filePath);

            // Skip directories
            if (stats.isDirectory()) continue;

            // Check extension filter
            if (extensions.length > 0) {
                const ext = path.extname(file).toLowerCase();
                if (!extensions.includes(ext)) continue;
            }

            // Check age
            const age = now - stats.mtimeMs;
            if (age > maxAge) {
                fs.unlinkSync(filePath);
                deleted++;
            }
        }
    } catch (error) {
        log(`Error cleaning ${directory}: ${error.message}`);
    }

    return deleted;
}

/**
 * Clean queue data file (remove old completed jobs)
 */
function cleanQueueData(queueFilePath, maxAgeHours = 1) {
    if (!fs.existsSync(queueFilePath)) {
        return { cleaned: 0, total: 0 };
    }

    try {
        const data = JSON.parse(fs.readFileSync(queueFilePath, 'utf8'));
        const now = Date.now();
        const maxAge = maxAgeHours * 60 * 60 * 1000;

        if (!data.jobs || !Array.isArray(data.jobs)) {
            return { cleaned: 0, total: 0 };
        }

        const originalCount = data.jobs.length;

        // Filter out old completed/failed jobs
        data.jobs = data.jobs.filter(([id, job]) => {
            if (!job.completedAt) return true;
            if (job.status !== 'completed' && job.status !== 'failed' && job.status !== 'cancelled') {
                return true;
            }
            return (now - job.completedAt) < maxAge;
        });

        const cleaned = originalCount - data.jobs.length;

        if (cleaned > 0) {
            fs.writeFileSync(queueFilePath, JSON.stringify(data, null, 2));
        }

        return { cleaned, total: originalCount };
    } catch (error) {
        log(`Error cleaning queue data: ${error.message}`);
        return { cleaned: 0, total: 0 };
    }
}

/**
 * Clean expired sessions
 */
function cleanExpiredSessions(sessionsDir, maxAgeDays = 7) {
    if (!fs.existsSync(sessionsDir)) {
        return 0;
    }

    const now = Date.now();
    const maxAge = maxAgeDays * 24 * 60 * 60 * 1000;
    let cleaned = 0;

    try {
        const dirs = fs.readdirSync(sessionsDir);

        for (const dir of dirs) {
            const sessionPath = path.join(sessionsDir, dir);
            const stats = fs.statSync(sessionPath);

            if (!stats.isDirectory()) continue;

            // Check session.json for last activity
            const metaFile = path.join(sessionPath, 'session.json');
            let shouldDelete = false;

            if (fs.existsSync(metaFile)) {
                try {
                    const meta = JSON.parse(fs.readFileSync(metaFile, 'utf8'));
                    if (meta.lastActive && (now - meta.lastActive) > maxAge) {
                        shouldDelete = true;
                    }
                } catch {
                    // If meta file is corrupt, check directory age
                    if ((now - stats.mtimeMs) > maxAge) {
                        shouldDelete = true;
                    }
                }
            } else {
                // No meta file, check directory age
                if ((now - stats.mtimeMs) > maxAge) {
                    shouldDelete = true;
                }
            }

            if (shouldDelete) {
                fs.rmSync(sessionPath, { recursive: true, force: true });
                cleaned++;
            }
        }
    } catch (error) {
        log(`Error cleaning sessions: ${error.message}`);
    }

    return cleaned;
}

/**
 * Get directory size
 */
function getDirectorySize(directory) {
    if (!fs.existsSync(directory)) return 0;

    let size = 0;
    try {
        const files = fs.readdirSync(directory);
        for (const file of files) {
            const filePath = path.join(directory, file);
            const stats = fs.statSync(filePath);
            if (stats.isFile()) {
                size += stats.size;
            } else if (stats.isDirectory()) {
                size += getDirectorySize(filePath);
            }
        }
    } catch {}
    return size;
}

/**
 * Format bytes to human readable
 */
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Main cleanup function
 */
async function main() {
    log('Starting cleanup...');

    const baseDir = path.join(__dirname, '../..');
    const results = {
        screenshots: 0,
        logs: 0,
        sessions: 0,
        queue: { cleaned: 0, total: 0 }
    };

    // 1. Clean old screenshots (older than 24 hours)
    const screenshotsDir = path.join(baseDir, 'logs/screenshots');
    results.screenshots = cleanOldFiles(screenshotsDir, 24, ['.png', '.jpg', '.jpeg']);
    log(`Screenshots: deleted ${results.screenshots} old files`);

    // 2. Clean old log files (older than 7 days)
    const logsDir = path.join(baseDir, 'logs');
    results.logs = cleanOldFiles(logsDir, 24 * 7, ['.log']);
    log(`Logs: deleted ${results.logs} old files`);

    // 3. Clean expired sessions (older than 7 days)
    const sessionsDir = path.join(baseDir, 'sessions');
    results.sessions = cleanExpiredSessions(sessionsDir, 7);
    log(`Sessions: cleaned ${results.sessions} expired sessions`);

    // 4. Clean queue data (completed jobs older than 1 hour)
    const queueFile = path.join(baseDir, 'data/queue.json');
    results.queue = cleanQueueData(queueFile, 1);
    log(`Queue: cleaned ${results.queue.cleaned}/${results.queue.total} old jobs`);

    // Report disk usage
    const diskUsage = {
        logs: formatBytes(getDirectorySize(logsDir)),
        sessions: formatBytes(getDirectorySize(sessionsDir)),
        data: formatBytes(getDirectorySize(path.join(baseDir, 'data')))
    };

    log('Cleanup complete!');
    log(`Disk usage - Logs: ${diskUsage.logs}, Sessions: ${diskUsage.sessions}, Data: ${diskUsage.data}`);

    return results;
}

// Run
main()
    .then(results => {
        console.log('\n' + JSON.stringify(results, null, 2));
        process.exit(0);
    })
    .catch(error => {
        log(`Fatal error: ${error.message}`);
        process.exit(1);
    });
