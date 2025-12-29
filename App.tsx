import React, {useState, useEffect} from 'react';
import {
  SafeAreaView,
  StatusBar,
  StyleSheet,
  ActivityIndicator,
  View,
} from 'react-native';
import LoginScreen from './src/screens/LoginScreen';
import TaskListScreen from './src/screens/TaskListScreen';
import TaskDetailScreen from './src/screens/TaskDetailScreen';
import AddEditTaskScreen from './src/screens/AddEditTaskScreen';
import AuthService from './src/services/AuthService';
import {Task} from './src/models/Task';

type Screen = 'login' | 'taskList' | 'taskDetail' | 'addTask' | 'editTask';

interface Navigation {
  screen: Screen;
  params?: {
    task?: Task;
  };
}

const App = () => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);
  const [navigation, setNavigation] = useState<Navigation>({
    screen: 'taskList',
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
    setNavigation({screen: 'taskList'});
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setNavigation({screen: 'login'});
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
    setNavigation({screen: 'taskList'});
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
      default:
        return (
          <TaskListScreen
            onTaskPress={navigateToTaskDetail}
            onAddPress={navigateToAddTask}
            onLogout={handleLogout}
          />
        );
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor="#fff" />
      {renderScreen()}
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
});

export default App;
