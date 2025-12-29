import React, {useState, useEffect} from 'react';
import {
  SafeAreaView,
  StatusBar,
  StyleSheet,
  ActivityIndicator,
  View,
  Text,
  TouchableOpacity,
} from 'react-native';
import LoginScreen from './src/screens/LoginScreen';
import TaskListScreen from './src/screens/TaskListScreen';
import TaskDetailScreen from './src/screens/TaskDetailScreen';
import AddEditTaskScreen from './src/screens/AddEditTaskScreen';
import DashboardScreen from './src/screens/DashboardScreen';
import NotificationsScreen from './src/screens/NotificationsScreen';
import ReportsScreen from './src/screens/ReportsScreen';
import GoalsScreen from './src/screens/GoalsScreen';
import AuthService from './src/services/AuthService';
import {Task} from './src/models/Task';

type Screen =
  | 'login'
  | 'dashboard'
  | 'taskList'
  | 'taskDetail'
  | 'addTask'
  | 'editTask'
  | 'notifications'
  | 'reports'
  | 'goals';

type Tab = 'dashboard' | 'tasks' | 'notifications' | 'reports' | 'goals';

interface Navigation {
  screen: Screen;
  params?: {
    task?: Task;
  };
}

const App = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<Tab>('dashboard');
  const [navigation, setNavigation] = useState<Navigation>({
    screen: 'dashboard',
  });

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    const authenticated = await AuthService.isAuthenticated();
    setIsAuthenticated(authenticated);
    setLoading(false);
  };

  const handleLoginSuccess = () => {
    setIsAuthenticated(true);
    setActiveTab('dashboard');
    setNavigation({screen: 'dashboard'});
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setActiveTab('dashboard');
    setNavigation({screen: 'login'});
  };

  const handleTabPress = (tab: Tab) => {
    setActiveTab(tab);
    switch (tab) {
      case 'dashboard':
        setNavigation({screen: 'dashboard'});
        break;
      case 'tasks':
        setNavigation({screen: 'taskList'});
        break;
      case 'notifications':
        setNavigation({screen: 'notifications'});
        break;
      case 'reports':
        setNavigation({screen: 'reports'});
        break;
      case 'goals':
        setNavigation({screen: 'goals'});
        break;
    }
  };

  const navigateToTaskDetail = (task: Task) => {
    setNavigation({screen: 'taskDetail', params: {task}});
  };

  const navigateToAddTask = () => {
    setNavigation({screen: 'addTask'});
  };

  const navigateToEditTask = (task: Task) => {
    setNavigation({screen: 'editTask', params: {task}});
  };

  const navigateToTaskList = () => {
    setActiveTab('tasks');
    setNavigation({screen: 'taskList'});
  };

  const navigateToDashboard = () => {
    setActiveTab('dashboard');
    setNavigation({screen: 'dashboard'});
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#007AFF" />
        </View>
      </SafeAreaView>
    );
  }

  const renderScreen = () => {
    if (!isAuthenticated) {
      return <LoginScreen onLoginSuccess={handleLoginSuccess} />;
    }

    switch (navigation.screen) {
      case 'dashboard':
        return <DashboardScreen />;
      case 'taskList':
        return (
          <TaskListScreen
            onTaskPress={navigateToTaskDetail}
            onAddPress={navigateToAddTask}
            onLogout={handleLogout}
          />
        );
      case 'taskDetail':
        return navigation.params?.task ? (
          <TaskDetailScreen
            task={navigation.params.task}
            onBack={navigateToTaskList}
            onEdit={navigateToEditTask}
            onDelete={navigateToTaskList}
          />
        ) : null;
      case 'addTask':
        return (
          <AddEditTaskScreen
            onBack={navigateToTaskList}
            onSave={navigateToTaskList}
          />
        );
      case 'editTask':
        return navigation.params?.task ? (
          <AddEditTaskScreen
            task={navigation.params.task}
            onBack={navigateToTaskList}
            onSave={navigateToTaskList}
          />
        ) : null;
      case 'notifications':
        return <NotificationsScreen />;
      case 'reports':
        return <ReportsScreen />;
      case 'goals':
        return <GoalsScreen />;
      default:
        return <DashboardScreen />;
    }
  };

  const renderBottomTabs = () => {
    if (!isAuthenticated) {
      return null;
    }

    // Hide tabs on detail screens
    if (
      navigation.screen === 'taskDetail' ||
      navigation.screen === 'addTask' ||
      navigation.screen === 'editTask'
    ) {
      return null;
    }

    return (
      <View style={styles.bottomTabs}>
        <TouchableOpacity
          style={[
            styles.tab,
            activeTab === 'dashboard' && styles.activeTab,
          ]}
          onPress={() => handleTabPress('dashboard')}>
          <Text
            style={[
              styles.tabText,
              activeTab === 'dashboard' && styles.activeTabText,
            ]}>
            📊
          </Text>
          <Text
            style={[
              styles.tabLabel,
              activeTab === 'dashboard' && styles.activeTabText,
            ]}>
            Dashboard
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.tab, activeTab === 'tasks' && styles.activeTab]}
          onPress={() => handleTabPress('tasks')}>
          <Text
            style={[
              styles.tabText,
              activeTab === 'tasks' && styles.activeTabText,
            ]}>
            ✓
          </Text>
          <Text
            style={[
              styles.tabLabel,
              activeTab === 'tasks' && styles.activeTabText,
            ]}>
            Tasks
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[
            styles.tab,
            activeTab === 'notifications' && styles.activeTab,
          ]}
          onPress={() => handleTabPress('notifications')}>
          <Text
            style={[
              styles.tabText,
              activeTab === 'notifications' && styles.activeTabText,
            ]}>
            🔔
          </Text>
          <Text
            style={[
              styles.tabLabel,
              activeTab === 'notifications' && styles.activeTabText,
            ]}>
            Alerts
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.tab, activeTab === 'reports' && styles.activeTab]}
          onPress={() => handleTabPress('reports')}>
          <Text
            style={[
              styles.tabText,
              activeTab === 'reports' && styles.activeTabText,
            ]}>
            📈
          </Text>
          <Text
            style={[
              styles.tabLabel,
              activeTab === 'reports' && styles.activeTabText,
            ]}>
            Reports
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.tab, activeTab === 'goals' && styles.activeTab]}
          onPress={() => handleTabPress('goals')}>
          <Text
            style={[
              styles.tabText,
              activeTab === 'goals' && styles.activeTabText,
            ]}>
            🎯
          </Text>
          <Text
            style={[
              styles.tabLabel,
              activeTab === 'goals' && styles.activeTabText,
            ]}>
            Goals
          </Text>
        </TouchableOpacity>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#fff" />
      {renderScreen()}
      {renderBottomTabs()}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  bottomTabs: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#eee',
    paddingBottom: 8,
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 8,
  },
  activeTab: {
    borderTopWidth: 2,
    borderTopColor: '#007AFF',
  },
  tabText: {
    fontSize: 24,
    marginBottom: 4,
  },
  tabLabel: {
    fontSize: 11,
    color: '#666',
  },
  activeTabText: {
    color: '#007AFF',
    fontWeight: '600',
  },
});

export default App;
