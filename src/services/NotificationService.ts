import ApiClient from './ApiClient';
import {API_ENDPOINTS} from '../config/api';

export interface Notification {
  id: string;
  title: string;
  message: string;
  type: 'info' | 'warning' | 'error' | 'success';
  read: boolean;
  createdAt: Date;
  relatedTaskId?: string;
  relatedUserId?: string;
}

/**
 * Service for managing notifications
 */
class NotificationService {
  /**
   * Get all notifications for the current user
   */
  async getNotifications(): Promise<Notification[]> {
    try {
      const response = await ApiClient.get<any[]>(
        API_ENDPOINTS.NOTIFICATIONS.GET_ALL,
        false,
      );
      return response.map((notif: any) => ({
        ...notif,
        createdAt: new Date(notif.createdAt || notif.created_at),
      }));
    } catch (error) {
      console.error('Error loading notifications:', error);
      throw error;
    }
  }

  /**
   * Mark a notification as read
   */
  async markAsRead(notificationId: string): Promise<void> {
    try {
      await ApiClient.post(
        API_ENDPOINTS.NOTIFICATIONS.MARK_READ(notificationId),
        {},
        false,
      );
    } catch (error) {
      console.error('Error marking notification as read:', error);
      throw error;
    }
  }

  /**
   * Mark all notifications as read
   */
  async markAllAsRead(): Promise<void> {
    try {
      const notifications = await this.getNotifications();
      const unreadNotifications = notifications.filter(n => !n.read);

      await Promise.all(
        unreadNotifications.map(n => this.markAsRead(n.id))
      );
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
      throw error;
    }
  }

  /**
   * Get unread notification count
   */
  async getUnreadCount(): Promise<number> {
    try {
      const notifications = await this.getNotifications();
      return notifications.filter(n => !n.read).length;
    } catch (error) {
      console.error('Error getting unread count:', error);
      return 0;
    }
  }
}

export default new NotificationService();
