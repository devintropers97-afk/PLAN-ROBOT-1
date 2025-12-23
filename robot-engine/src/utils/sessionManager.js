/**
 * Session Manager - Manages browser sessions for multiple traders
 * Prevents captcha by reusing sessions and limiting login attempts
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

class SessionManager {
    constructor(options = {}) {
        this.baseSessionDir = options.sessionDir || path.join(__dirname, '../../sessions');
        this.sessionTimeout = options.sessionTimeout || 24 * 60 * 60 * 1000; // 24 hours
        this.maxConcurrentSessions = options.maxConcurrentSessions || 5;
        this.activeSessions = new Map();

        // Ensure base directory exists
        if (!fs.existsSync(this.baseSessionDir)) {
            fs.mkdirSync(this.baseSessionDir, { recursive: true });
        }
    }

    /**
     * Generate unique session ID for trader
     */
    generateSessionId(email) {
        return crypto.createHash('md5').update(email).digest('hex').substring(0, 12);
    }

    /**
     * Get session directory for a trader
     */
    getSessionDir(email) {
        const sessionId = this.generateSessionId(email);
        const sessionDir = path.join(this.baseSessionDir, sessionId);

        if (!fs.existsSync(sessionDir)) {
            fs.mkdirSync(sessionDir, { recursive: true });
        }

        return sessionDir;
    }

    /**
     * Check if session is valid (not expired)
     */
    isSessionValid(email) {
        const sessionDir = this.getSessionDir(email);
        const metaFile = path.join(sessionDir, 'session.json');

        if (!fs.existsSync(metaFile)) {
            return false;
        }

        try {
            const meta = JSON.parse(fs.readFileSync(metaFile, 'utf8'));
            const isValid = Date.now() - meta.lastActive < this.sessionTimeout;
            return isValid;
        } catch {
            return false;
        }
    }

    /**
     * Update session metadata
     */
    updateSession(email, data = {}) {
        const sessionDir = this.getSessionDir(email);
        const metaFile = path.join(sessionDir, 'session.json');

        let meta = {
            email,
            createdAt: Date.now(),
            lastActive: Date.now(),
            loginCount: 0,
            ...data
        };

        if (fs.existsSync(metaFile)) {
            try {
                const existing = JSON.parse(fs.readFileSync(metaFile, 'utf8'));
                meta = { ...existing, ...data, lastActive: Date.now() };
            } catch {}
        }

        fs.writeFileSync(metaFile, JSON.stringify(meta, null, 2));
        return meta;
    }

    /**
     * Get session metadata
     */
    getSessionMeta(email) {
        const sessionDir = this.getSessionDir(email);
        const metaFile = path.join(sessionDir, 'session.json');

        if (!fs.existsSync(metaFile)) {
            return null;
        }

        try {
            return JSON.parse(fs.readFileSync(metaFile, 'utf8'));
        } catch {
            return null;
        }
    }

    /**
     * Record login attempt
     */
    recordLogin(email, success) {
        const meta = this.getSessionMeta(email) || {};
        const loginCount = (meta.loginCount || 0) + 1;

        this.updateSession(email, {
            loginCount,
            lastLoginAttempt: Date.now(),
            lastLoginSuccess: success
        });

        return loginCount;
    }

    /**
     * Check if we should avoid login (too many recent attempts)
     */
    shouldAvoidLogin(email) {
        const meta = this.getSessionMeta(email);
        if (!meta) return false;

        // If last login was successful and session is valid, avoid re-login
        if (meta.lastLoginSuccess && this.isSessionValid(email)) {
            return true;
        }

        // If too many login attempts in last hour, wait
        if (meta.loginCount >= 3) {
            const hourAgo = Date.now() - 3600000;
            if (meta.lastLoginAttempt > hourAgo) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get wait time before next login attempt
     */
    getWaitTime(email) {
        const meta = this.getSessionMeta(email);
        if (!meta) return 0;

        if (meta.loginCount >= 3) {
            const hourAgo = Date.now() - 3600000;
            if (meta.lastLoginAttempt > hourAgo) {
                return meta.lastLoginAttempt - hourAgo;
            }
        }

        return 0;
    }

    /**
     * Clear session for trader
     */
    clearSession(email) {
        const sessionDir = this.getSessionDir(email);

        if (fs.existsSync(sessionDir)) {
            fs.rmSync(sessionDir, { recursive: true, force: true });
        }
    }

    /**
     * Get all active sessions
     */
    getAllSessions() {
        const sessions = [];

        try {
            const dirs = fs.readdirSync(this.baseSessionDir);
            for (const dir of dirs) {
                const metaFile = path.join(this.baseSessionDir, dir, 'session.json');
                if (fs.existsSync(metaFile)) {
                    try {
                        const meta = JSON.parse(fs.readFileSync(metaFile, 'utf8'));
                        sessions.push({
                            ...meta,
                            sessionId: dir,
                            isValid: Date.now() - meta.lastActive < this.sessionTimeout
                        });
                    } catch {}
                }
            }
        } catch {}

        return sessions;
    }

    /**
     * Cleanup expired sessions
     */
    cleanupExpiredSessions() {
        const sessions = this.getAllSessions();
        let cleaned = 0;

        for (const session of sessions) {
            if (!session.isValid) {
                const sessionDir = path.join(this.baseSessionDir, session.sessionId);
                fs.rmSync(sessionDir, { recursive: true, force: true });
                cleaned++;
            }
        }

        return cleaned;
    }
}

module.exports = SessionManager;
