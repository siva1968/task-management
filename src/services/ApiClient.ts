import {API_CONFIG} from '../config/api';
import AuthService from './AuthService';

export interface ApiResponse<T> {
  data?: T;
  error?: string;
  success: boolean;
}

/**
 * HTTP Methods
 */
export enum HttpMethod {
  GET = 'GET',
  POST = 'POST',
  PUT = 'PUT',
  DELETE = 'DELETE',
}

/**
 * API Client for making authenticated requests
 */
class ApiClient {
  private baseUrl: string;
  private mobileBaseUrl: string;

  constructor() {
    this.baseUrl = API_CONFIG.BASE_URL;
    this.mobileBaseUrl = API_CONFIG.MOBILE_BASE_URL;
  }

  /**
   * Make an authenticated API request
   */
  async request<T>(
    endpoint: string,
    method: HttpMethod = HttpMethod.GET,
    data?: any,
    useMobileApi: boolean = true,
  ): Promise<T> {
    const url = `${useMobileApi ? this.mobileBaseUrl : this.baseUrl}${endpoint}`;
    const token = await AuthService.getToken();

    const headers: HeadersInit = {
      'Content-Type': 'application/json',
    };

    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    const config: RequestInit = {
      method,
      headers,
    };

    if (data && (method === HttpMethod.POST || method === HttpMethod.PUT)) {
      config.body = JSON.stringify(data);
    }

    try {
      const response = await fetch(url, config);

      // Handle 401 Unauthorized - try to refresh token
      if (response.status === 401) {
        try {
          await AuthService.refreshToken();
          // Retry the request with new token
          const newToken = await AuthService.getToken();
          if (newToken) {
            headers.Authorization = `Bearer ${newToken}`;
            const retryResponse = await fetch(url, {...config, headers});
            if (!retryResponse.ok) {
              throw new Error(`API Error: ${retryResponse.statusText}`);
            }
            return await retryResponse.json();
          }
        } catch (refreshError) {
          // Refresh failed, redirect to login
          throw new Error('Session expired. Please login again.');
        }
      }

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `API Error: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  /**
   * GET request
   */
  async get<T>(endpoint: string, useMobileApi: boolean = true): Promise<T> {
    return this.request<T>(endpoint, HttpMethod.GET, undefined, useMobileApi);
  }

  /**
   * POST request
   */
  async post<T>(endpoint: string, data: any, useMobileApi: boolean = true): Promise<T> {
    return this.request<T>(endpoint, HttpMethod.POST, data, useMobileApi);
  }

  /**
   * PUT request
   */
  async put<T>(endpoint: string, data: any, useMobileApi: boolean = true): Promise<T> {
    return this.request<T>(endpoint, HttpMethod.PUT, data, useMobileApi);
  }

  /**
   * DELETE request
   */
  async delete<T>(endpoint: string, useMobileApi: boolean = true): Promise<T> {
    return this.request<T>(endpoint, HttpMethod.DELETE, undefined, useMobileApi);
  }
}

export default new ApiClient();
