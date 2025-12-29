import AsyncStorage from '@react-native-async-storage/async-storage';
import {Task, CreateTaskInput, UpdateTaskInput, TaskStatus} from '../models/Task';

const TASKS_STORAGE_KEY = '@tasks';

/**
 * Service for managing tasks with AsyncStorage persistence
 */
class TaskService {
  /**
   * Get all tasks from storage
   */
  async getTasks(): Promise<Task[]> {
    try {
      const tasksJson = await AsyncStorage.getItem(TASKS_STORAGE_KEY);
      if (!tasksJson) {
        return [];
      }
      const tasks = JSON.parse(tasksJson);
      // Convert date strings back to Date objects
      return tasks.map((task: any) => ({
        ...task,
        createdAt: new Date(task.createdAt),
        updatedAt: new Date(task.updatedAt),
        dueDate: task.dueDate ? new Date(task.dueDate) : undefined,
      }));
    } catch (error) {
      console.error('Error loading tasks:', error);
      return [];
    }
  }

  /**
   * Get a single task by ID
   */
  async getTaskById(id: string): Promise<Task | null> {
    const tasks = await this.getTasks();
    return tasks.find(task => task.id === id) || null;
  }

  /**
   * Create a new task
   */
  async createTask(input: CreateTaskInput): Promise<Task> {
    const tasks = await this.getTasks();
    const newTask: Task = {
      ...input,
      id: Date.now().toString(),
      createdAt: new Date(),
      updatedAt: new Date(),
    };
    tasks.push(newTask);
    await this.saveTasks(tasks);
    return newTask;
  }

  /**
   * Update an existing task
   */
  async updateTask(input: UpdateTaskInput): Promise<Task | null> {
    const tasks = await this.getTasks();
    const index = tasks.findIndex(task => task.id === input.id);

    if (index === -1) {
      return null;
    }

    const updatedTask: Task = {
      ...tasks[index],
      ...input,
      updatedAt: new Date(),
    };

    tasks[index] = updatedTask;
    await this.saveTasks(tasks);
    return updatedTask;
  }

  /**
   * Delete a task by ID
   */
  async deleteTask(id: string): Promise<boolean> {
    const tasks = await this.getTasks();
    const filteredTasks = tasks.filter(task => task.id !== id);

    if (filteredTasks.length === tasks.length) {
      return false; // Task not found
    }

    await this.saveTasks(filteredTasks);
    return true;
  }

  /**
   * Archive a task (set status to archived)
   */
  async archiveTask(id: string): Promise<Task | null> {
    return this.updateTask({id, status: TaskStatus.ARCHIVED});
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
   * Save tasks to storage
   */
  private async saveTasks(tasks: Task[]): Promise<void> {
    try {
      await AsyncStorage.setItem(TASKS_STORAGE_KEY, JSON.stringify(tasks));
    } catch (error) {
      console.error('Error saving tasks:', error);
      throw error;
    }
  }

  /**
   * Clear all tasks (useful for development/testing)
   */
  async clearAllTasks(): Promise<void> {
    await AsyncStorage.removeItem(TASKS_STORAGE_KEY);
  }
}

export default new TaskService();
