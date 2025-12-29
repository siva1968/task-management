import ApiClient from './ApiClient';
import {API_ENDPOINTS} from '../config/api';

export interface DashboardSummary {
  totalTasks: number;
  completedTasks: number;
  pendingTasks: number;
  inProgressTasks: number;
  overdueT asks: number;
  upcomingDeadlines: number;
}

export interface TeamMember {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  assignedTasks: number;
  completedTasks: number;
  workloadPercentage: number;
}

export interface TrendData {
  date: string;
  completed: number;
  created: number;
  inProgress: number;
}

/**
 * Service for dashboard data and analytics
 */
class DashboardService {
  /**
   * Get dashboard summary statistics
   */
  async getSummary(): Promise<DashboardSummary> {
    try {
      const response = await ApiClient.get<DashboardSummary>(
        API_ENDPOINTS.DASHBOARD.SUMMARY,
        false, // use main API, not mobile API
      );
      return response;
    } catch (error) {
      console.error('Error loading dashboard summary:', error);
      throw error;
    }
  }

  /**
   * Get team workload data
   */
  async getTeamWorkload(): Promise<TeamMember[]> {
    try {
      const response = await ApiClient.get<TeamMember[]>(
        API_ENDPOINTS.DASHBOARD.TEAM_WORKLOAD,
        false,
      );
      return response;
    } catch (error) {
      console.error('Error loading team workload:', error);
      throw error;
    }
  }

  /**
   * Get trends data for charts
   */
  async getTrends(days: number = 30): Promise<TrendData[]> {
    try {
      const response = await ApiClient.get<TrendData[]>(
        `${API_ENDPOINTS.DASHBOARD.TRENDS}?days=${days}`,
        false,
      );
      return response;
    } catch (error) {
      console.error('Error loading trends:', error);
      throw error;
    }
  }
}

export default new DashboardService();
