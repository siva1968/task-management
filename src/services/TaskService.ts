import {Task, CreateTaskInput, UpdateTaskInput, TaskStatus} from '../models/Task';
import ApiClient from './ApiClient';
import {API_ENDPOINTS} from '../config/api';

/**
 * Service for managing tasks via REST API
 */
class TaskService {
  /**
   * Get all tasks from API
   */
  async getTasks(): Promise<Task[]> {
    try {
      const response = await ApiClient.get<Task[]>(API_ENDPOINTS.TASKS.GET_ALL);
      // Convert date strings to Date objects
      return response.map((task: any) => ({
        ...task,
        createdAt: new Date(task.createdAt || task.created_at),
        updatedAt: new Date(task.updatedAt || task.updated_at),
        dueDate: task.dueDate || task.due_date ? new Date(task.dueDate || task.due_date) : undefined,
      }));
    } catch (error) {
      console.error('Error loading tasks:', error);
      throw error;
    }
  }

  /**
   * Get a single task by ID
   */
  async getTaskById(id: string): Promise<Task | null> {
    try {
      const response = await ApiClient.get<Task>(API_ENDPOINTS.TASKS.GET_BY_ID(id));
      return {
        ...response,
        createdAt: new Date((response as any).createdAt || (response as any).created_at),
        updatedAt: new Date((response as any).updatedAt || (response as any).updated_at),
        dueDate: (response as any).dueDate || (response as any).due_date
          ? new Date((response as any).dueDate || (response as any).due_date)
          : undefined,
      };
    } catch (error) {
      console.error('Error loading task:', error);
      return null;
    }
  }

  /**
   * Create a new task
   */
  async createTask(input: CreateTaskInput): Promise<Task> {
    try {
      const response = await ApiClient.post<Task>(API_ENDPOINTS.TASKS.CREATE, input);
      return {
        ...response,
        createdAt: new Date((response as any).createdAt || (response as any).created_at),
        updatedAt: new Date((response as any).updatedAt || (response as any).updated_at),
        dueDate: (response as any).dueDate || (response as any).due_date
          ? new Date((response as any).dueDate || (response as any).due_date)
          : undefined,
      };
    } catch (error) {
      console.error('Error creating task:', error);
      throw error;
    }
  }

  /**
   * Update an existing task
   */
  async updateTask(input: UpdateTaskInput): Promise<Task | null> {
    try {
      const response = await ApiClient.put<Task>(
        API_ENDPOINTS.TASKS.UPDATE(input.id),
        input,
      );
      return {
        ...response,
        createdAt: new Date((response as any).createdAt || (response as any).created_at),
        updatedAt: new Date((response as any).updatedAt || (response as any).updated_at),
        dueDate: (response as any).dueDate || (response as any).due_date
          ? new Date((response as any).dueDate || (response as any).due_date)
          : undefined,
      };
    } catch (error) {
      console.error('Error updating task:', error);
      return null;
    }
  }

  /**
   * Delete a task by ID
   */
  async deleteTask(id: string): Promise<boolean> {
    try {
      await ApiClient.delete(API_ENDPOINTS.TASKS.DELETE(id));
      return true;
    } catch (error) {
      console.error('Error deleting task:', error);
      return false;
    }
  }

  /**
   * Update task status
   */
  async updateTaskStatus(id: string, status: TaskStatus): Promise<Task | null> {
    try {
      const response = await ApiClient.post<Task>(
        API_ENDPOINTS.TASKS.UPDATE_STATUS(id),
        {status},
      );
      return {
        ...response,
        createdAt: new Date((response as any).createdAt || (response as any).created_at),
        updatedAt: new Date((response as any).updatedAt || (response as any).updated_at),
        dueDate: (response as any).dueDate || (response as any).due_date
          ? new Date((response as any).dueDate || (response as any).due_date)
          : undefined,
      };
    } catch (error) {
      console.error('Error updating task status:', error);
      return null;
    }
  }

  /**
   * Archive a task (set status to archived)
   */
  async archiveTask(id: string): Promise<Task | null> {
    return this.updateTaskStatus(id, TaskStatus.ARCHIVED);
  }

  /**
   * Get tasks filtered by status
   */
  async getTasksByStatus(status: TaskStatus): Promise<Task[]> {
    const tasks = await this.getTasks();
    return tasks.filter(task => task.status === status);
  }

  /**
   * Search tasks by title or description
   */
  async searchTasks(query: string): Promise<Task[]> {
    const tasks = await this.getTasks();
    const lowerQuery = query.toLowerCase();
    return tasks.filter(
      task =>
        task.title.toLowerCase().includes(lowerQuery) ||
        task.description.toLowerCase().includes(lowerQuery),
    );
  }

  /**
   * Log time entry for a task
   */
  async logTime(id: string, timeData: {hours: number; description?: string}): Promise<void> {
    try {
      await ApiClient.post(API_ENDPOINTS.TASKS.LOG_TIME(id), timeData);
    } catch (error) {
      console.error('Error logging time:', error);
      throw error;
    }
  }
}

export default new TaskService();
