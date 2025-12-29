import ApiClient from './ApiClient';
import {API_ENDPOINTS} from '../config/api';

export interface TaskReport {
  taskId: string;
  taskTitle: string;
  status: string;
  priority: string;
  assignee: string;
  dueDate?: Date;
  completedDate?: Date;
  timeSpent: number;
}

export interface TimeTrackingReport {
  userId: string;
  userName: string;
  taskId: string;
  taskTitle: string;
  hours: number;
  date: Date;
  description?: string;
}

export interface ClientReport {
  clientId: string;
  clientName: string;
  totalTasks: number;
  completedTasks: number;
  totalHours: number;
  revenue?: number;
}

export interface TeamReport {
  teamMemberId: string;
  teamMemberName: string;
  tasksCompleted: number;
  tasksInProgress: number;
  totalHours: number;
  productivity: number;
}

export interface ReportFilters {
  startDate?: string;
  endDate?: string;
  status?: string;
  priority?: string;
  assignee?: string;
  clientId?: string;
}

export interface ExportOptions {
  format: 'pdf' | 'csv' | 'excel';
  reportType: 'tasks' | 'time-tracking' | 'client' | 'team';
  filters?: ReportFilters;
}

export interface ScheduleReportOptions {
  reportType: 'tasks' | 'time-tracking' | 'client' | 'team';
  frequency: 'daily' | 'weekly' | 'monthly';
  recipients: string[];
  filters?: ReportFilters;
}

/**
 * Service for generating and managing reports
 */
class ReportService {
  /**
   * Get task reports with optional filters
   */
  async getTaskReports(filters?: ReportFilters): Promise<TaskReport[]> {
    try {
      const queryParams = this.buildQueryParams(filters);
      const response = await ApiClient.get<any[]>(
        `${API_ENDPOINTS.REPORTS.TASKS}${queryParams}`,
        false,
      );
      return response.map((report: any) => ({
        ...report,
        dueDate: report.dueDate ? new Date(report.dueDate) : undefined,
        completedDate: report.completedDate
          ? new Date(report.completedDate)
          : undefined,
      }));
    } catch (error) {
      console.error('Error loading task reports:', error);
      throw error;
    }
  }

  /**
   * Get time tracking reports
   */
  async getTimeTrackingReports(
    filters?: ReportFilters,
  ): Promise<TimeTrackingReport[]> {
    try {
      const queryParams = this.buildQueryParams(filters);
      const response = await ApiClient.get<any[]>(
        `${API_ENDPOINTS.REPORTS.TIME_TRACKING}${queryParams}`,
        false,
      );
      return response.map((report: any) => ({
        ...report,
        date: new Date(report.date),
      }));
    } catch (error) {
      console.error('Error loading time tracking reports:', error);
      throw error;
    }
  }

  /**
   * Get client report
   */
  async getClientReport(clientId: string): Promise<ClientReport> {
    try {
      const response = await ApiClient.get<ClientReport>(
        API_ENDPOINTS.REPORTS.CLIENT(clientId),
        false,
      );
      return response;
    } catch (error) {
      console.error('Error loading client report:', error);
      throw error;
    }
  }

  /**
   * Get team reports
   */
  async getTeamReports(filters?: ReportFilters): Promise<TeamReport[]> {
    try {
      const queryParams = this.buildQueryParams(filters);
      const response = await ApiClient.get<TeamReport[]>(
        `${API_ENDPOINTS.REPORTS.TEAM}${queryParams}`,
        false,
      );
      return response;
    } catch (error) {
      console.error('Error loading team reports:', error);
      throw error;
    }
  }

  /**
   * Export report in specified format
   */
  async exportReport(options: ExportOptions): Promise<{url: string}> {
    try {
      const response = await ApiClient.post<{url: string}>(
        API_ENDPOINTS.REPORTS.EXPORT,
        options,
        false,
      );
      return response;
    } catch (error) {
      console.error('Error exporting report:', error);
      throw error;
    }
  }

  /**
   * Schedule recurring report
   */
  async scheduleReport(options: ScheduleReportOptions): Promise<{id: string}> {
    try {
      const response = await ApiClient.post<{id: string}>(
        API_ENDPOINTS.REPORTS.SCHEDULE,
        options,
        false,
      );
      return response;
    } catch (error) {
      console.error('Error scheduling report:', error);
      throw error;
    }
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: ReportFilters): string {
    if (!filters) {
      return '';
    }

    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value) {
        params.append(key, value.toString());
      }
    });

    const queryString = params.toString();
    return queryString ? `?${queryString}` : '';
  }
}

export default new ReportService();
