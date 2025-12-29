import ApiClient from './ApiClient';
import {API_ENDPOINTS} from '../config/api';

export interface KPI {
  id: string;
  name: string;
  value: number;
  target: number;
  unit: string;
  percentage: number;
  trend: 'up' | 'down' | 'stable';
}

export interface Goal {
  id: string;
  title: string;
  description: string;
  targetDate: Date;
  progress: number;
  status: 'not_started' | 'in_progress' | 'completed' | 'overdue';
  priority: 'low' | 'medium' | 'high';
  employeeId: string;
  employeeName: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface CreateGoalInput {
  title: string;
  description: string;
  targetDate: Date;
  priority: 'low' | 'medium' | 'high';
  employeeId: string;
}

/**
 * Service for managing employee goals and KPIs
 */
class GoalService {
  /**
   * Get KPIs for an employee
   */
  async getEmployeeKPIs(employeeId: string): Promise<KPI[]> {
    try {
      const response = await ApiClient.get<KPI[]>(
        API_ENDPOINTS.EMPLOYEES.KPIS(employeeId),
        false,
      );
      return response;
    } catch (error) {
      console.error('Error loading employee KPIs:', error);
      throw error;
    }
  }

  /**
   * Get goals for an employee
   */
  async getEmployeeGoals(employeeId: string): Promise<Goal[]> {
    try {
      const response = await ApiClient.get<any[]>(
        API_ENDPOINTS.EMPLOYEES.GOALS(employeeId),
        false,
      );
      return response.map((goal: any) => ({
        ...goal,
        targetDate: new Date(goal.targetDate || goal.target_date),
        createdAt: new Date(goal.createdAt || goal.created_at),
        updatedAt: new Date(goal.updatedAt || goal.updated_at),
      }));
    } catch (error) {
      console.error('Error loading employee goals:', error);
      throw error;
    }
  }

  /**
   * Create a new goal
   */
  async createGoal(input: CreateGoalInput): Promise<Goal> {
    try {
      const response = await ApiClient.post<any>(
        API_ENDPOINTS.GOALS.CREATE,
        input,
        false,
      );
      return {
        ...response,
        targetDate: new Date(response.targetDate || response.target_date),
        createdAt: new Date(response.createdAt || response.created_at),
        updatedAt: new Date(response.updatedAt || response.updated_at),
      };
    } catch (error) {
      console.error('Error creating goal:', error);
      throw error;
    }
  }

  /**
   * Get current user's KPIs
   */
  async getMyKPIs(): Promise<KPI[]> {
    try {
      // Assuming the API returns current user's ID or we have it stored
      const user = await this.getCurrentUser();
      return this.getEmployeeKPIs(user.id);
    } catch (error) {
      console.error('Error loading my KPIs:', error);
      throw error;
    }
  }

  /**
   * Get current user's goals
   */
  async getMyGoals(): Promise<Goal[]> {
    try {
      const user = await this.getCurrentUser();
      return this.getEmployeeGoals(user.id);
    } catch (error) {
      console.error('Error loading my goals:', error);
      throw error;
    }
  }

  /**
   * Get current user (helper method)
   */
  private async getCurrentUser(): Promise<{id: string}> {
    // This should get the user from AuthService
    const AuthService = require('./AuthService').default;
    const user = await AuthService.getUser();
    if (!user) {
      throw new Error('User not authenticated');
    }
    return user;
  }
}

export default new GoalService();
