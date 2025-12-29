import AsyncStorage from '@react-native-async-storage/async-storage';
import {API_CONFIG, API_ENDPOINTS} from '../config/api';

const TOKEN_KEY = '@auth_token';
const REFRESH_TOKEN_KEY = '@refresh_token';
const USER_KEY = '@user_data';

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface AuthTokens {
  token: string;
  refreshToken: string;
}

export interface User {
  id: string;
  username: string;
  email: string;
  displayName: string;
}

export interface LoginResponse {
  token: string;
  refresh_token: string;
  user: User;
}

/**
 * Authentication Service for managing user login, tokens, and authentication state
 */
class AuthService {
  /**
   * Login user with credentials
   */
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    try {
      const response = await fetch(
        `${API_CONFIG.MOBILE_BASE_URL}${API_ENDPOINTS.AUTH.LOGIN}`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(credentials),
        },
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || 'Login failed');
      }

      const data: LoginResponse = await response.json();

      // Store tokens and user data
      await this.storeTokens({
        token: data.token,
        refreshToken: data.refresh_token,
      });
      await this.storeUser(data.user);

      return data;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  /**
   * Logout user and clear stored data
   */
  async logout(): Promise<void> {
    await AsyncStorage.multiRemove([TOKEN_KEY, REFRESH_TOKEN_KEY, USER_KEY]);
  }

  /**
   * Get stored authentication token
   */
  async getToken(): Promise<string | null> {
    return AsyncStorage.getItem(TOKEN_KEY);
  }

  /**
   * Get stored refresh token
   */
  async getRefreshToken(): Promise<string | null> {
    return AsyncStorage.getItem(REFRESH_TOKEN_KEY);
  }

  /**
   * Store authentication tokens
   */
  async storeTokens(tokens: AuthTokens): Promise<void> {
    await AsyncStorage.multiSet([
      [TOKEN_KEY, tokens.token],
      [REFRESH_TOKEN_KEY, tokens.refreshToken],
    ]);
  }

  /**
   * Get stored user data
   */
  async getUser(): Promise<User | null> {
    const userData = await AsyncStorage.getItem(USER_KEY);
    return userData ? JSON.parse(userData) : null;
  }

  /**
   * Store user data
   */
  async storeUser(user: User): Promise<void> {
    await AsyncStorage.setItem(USER_KEY, JSON.stringify(user));
  }

  /**
   * Check if user is authenticated
   */
  async isAuthenticated(): Promise<boolean> {
    const token = await this.getToken();
    return !!token;
  }

  /**
   * Refresh the authentication token
   */
  async refreshToken(): Promise<string> {
    const refreshToken = await this.getRefreshToken();

    if (!refreshToken) {
      throw new Error('No refresh token available');
    }

    try {
      const response = await fetch(
        `${API_CONFIG.MOBILE_BASE_URL}${API_ENDPOINTS.AUTH.REFRESH}`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({refresh_token: refreshToken}),
        },
      );

      if (!response.ok) {
        throw new Error('Token refresh failed');
      }

      const data = await response.json();
      await this.storeTokens({
        token: data.token,
        refreshToken: data.refresh_token,
      });

      return data.token;
    } catch (error) {
      console.error('Token refresh error:', error);
      await this.logout();
      throw error;
    }
  }
}

export default new AuthService();
