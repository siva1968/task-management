/**
 * API Configuration
 */
export const API_CONFIG = {
  BASE_URL: 'https://tasks.getinstantleads.in/wp-json/sdm/v1',
  MOBILE_BASE_URL: 'https://tasks.getinstantleads.in/wp-json/sdm-mobile/v1',
  TIMEOUT: 30000,
};

/**
 * API Endpoints
 */
export const API_ENDPOINTS = {
  // Authentication
  AUTH: {
    LOGIN: '/auth/login',
    REFRESH: '/auth/refresh',
  },
  // Tasks
  TASKS: {
    GET_ALL: '/tasks',
    GET_BY_ID: (id: string) => `/tasks/${id}`,
    CREATE: '/tasks',
    UPDATE: (id: string) => `/tasks/${id}`,
    DELETE: (id: string) => `/tasks/${id}`,
    UPDATE_STATUS: (id: string) => `/tasks/${id}/status`,
    LOG_TIME: (id: string) => `/tasks/${id}/time`,
  },
  // Dashboard
  DASHBOARD: {
    SUMMARY: '/dashboard/summary',
    TEAM_WORKLOAD: '/dashboard/team-workload',
    TRENDS: '/dashboard/trends',
  },
  // Notifications
  NOTIFICATIONS: {
    GET_ALL: '/notifications',
    MARK_READ: (id: string) => `/notifications/${id}/read`,
  },
  // Reports
  REPORTS: {
    TASKS: '/reports/tasks',
    TIME_TRACKING: '/reports/time-tracking',
    CLIENT: (id: string) => `/reports/client/${id}`,
    TEAM: '/reports/team',
    EXPORT: '/reports/export',
    SCHEDULE: '/reports/schedule',
  },
  // Employees/Goals
  EMPLOYEES: {
    KPIS: (id: string) => `/employees/${id}/kpis`,
    GOALS: (id: string) => `/employees/${id}/goals`,
  },
  GOALS: {
    CREATE: '/goals',
  },
};
