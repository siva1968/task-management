import React, {useState, useEffect, useCallback} from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  RefreshControl,
  Alert,
  TextInput,
  Modal,
} from 'react-native';
import GoalService, {KPI, Goal, CreateGoalInput} from '../services/GoalService';

const GoalsScreen: React.FC = () => {
  const [kpis, setKpis] = useState<KPI[]>([]);
  const [goals, setGoals] = useState<Goal[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [showAddGoal, setShowAddGoal] = useState(false);
  const [newGoal, setNewGoal] = useState<Partial<CreateGoalInput>>({
    priority: 'medium',
  });

  const loadData = useCallback(async () => {
    try {
      const [kpisData, goalsData] = await Promise.all([
        GoalService.getMyKPIs(),
        GoalService.getMyGoals(),
      ]);
      setKpis(kpisData);
      setGoals(goalsData);
    } catch (error: any) {
      Alert.alert('Error', 'Failed to load goals and KPIs');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleCreateGoal = async () => {
    if (!newGoal.title || !newGoal.description || !newGoal.targetDate) {
      Alert.alert('Error', 'Please fill in all required fields');
      return;
    }

    try {
      const user = await GoalService['getCurrentUser']();
      const goalInput: CreateGoalInput = {
        title: newGoal.title,
        description: newGoal.description,
        targetDate: newGoal.targetDate,
        priority: newGoal.priority as 'low' | 'medium' | 'high',
        employeeId: user.id,
      };
      await GoalService.createGoal(goalInput);
      setShowAddGoal(false);
      setNewGoal({priority: 'medium'});
      loadData();
      Alert.alert('Success', 'Goal created successfully');
    } catch (error) {
      Alert.alert('Error', 'Failed to create goal');
    }
  };

  const getTrendIcon = (trend: KPI['trend']): string => {
    switch (trend) {
      case 'up':
        return '↑';
      case 'down':
        return '↓';
      case 'stable':
        return '→';
    }
  };

  const getTrendColor = (trend: KPI['trend']): string => {
    switch (trend) {
      case 'up':
        return '#34C759';
      case 'down':
        return '#FF3B30';
      case 'stable':
        return '#FF9500';
    }
  };

  const getGoalStatusColor = (status: Goal['status']): string => {
    switch (status) {
      case 'completed':
        return '#34C759';
      case 'in_progress':
        return '#007AFF';
      case 'overdue':
        return '#FF3B30';
      case 'not_started':
      default:
        return '#999';
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Goals & KPIs</Text>
        <TouchableOpacity
          style={styles.addButton}
          onPress={() => setShowAddGoal(true)}>
          <Text style={styles.addButtonText}>+ New Goal</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }>
        {kpis.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Key Performance Indicators</Text>
            {kpis.map(kpi => (
              <View key={kpi.id} style={styles.kpiCard}>
                <View style={styles.kpiHeader}>
                  <Text style={styles.kpiName}>{kpi.name}</Text>
                  <View
                    style={[
                      styles.trendBadge,
                      {backgroundColor: getTrendColor(kpi.trend)},
                    ]}>
                    <Text style={styles.trendText}>
                      {getTrendIcon(kpi.trend)} {kpi.trend}
                    </Text>
                  </View>
                </View>
                <View style={styles.kpiValues}>
                  <View>
                    <Text style={styles.kpiValueLabel}>Current</Text>
                    <Text style={styles.kpiValue}>
                      {kpi.value} {kpi.unit}
                    </Text>
                  </View>
                  <View>
                    <Text style={styles.kpiValueLabel}>Target</Text>
                    <Text style={styles.kpiValue}>
                      {kpi.target} {kpi.unit}
                    </Text>
                  </View>
                </View>
                <View style={styles.progressBarContainer}>
                  <View
                    style={[styles.progressBar, {width: `${kpi.percentage}%`}]}
                  />
                </View>
                <Text style={styles.percentageText}>{kpi.percentage}%</Text>
              </View>
            ))}
          </View>
        )}

        {goals.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>My Goals</Text>
            {goals.map(goal => (
              <View key={goal.id} style={styles.goalCard}>
                <View style={styles.goalHeader}>
                  <Text style={styles.goalTitle}>{goal.title}</Text>
                  <View
                    style={[
                      styles.statusBadge,
                      {backgroundColor: getGoalStatusColor(goal.status)},
                    ]}>
                    <Text style={styles.statusText}>
                      {goal.status.replace('_', ' ')}
                    </Text>
                  </View>
                </View>
                <Text style={styles.goalDescription}>{goal.description}</Text>
                <View style={styles.goalMeta}>
                  <Text style={styles.goalMetaText}>
                    Due: {goal.targetDate.toLocaleDateString()}
                  </Text>
                  <Text style={styles.goalMetaText}>
                    Priority: {goal.priority}
                  </Text>
                </View>
                <View style={styles.progressBarContainer}>
                  <View
                    style={[
                      styles.progressBar,
                      {width: `${goal.progress}%`},
                    ]}
                  />
                </View>
                <Text style={styles.percentageText}>{goal.progress}%</Text>
              </View>
            ))}
          </View>
        )}
      </ScrollView>

      <Modal
        visible={showAddGoal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowAddGoal(false)}>
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Create New Goal</Text>

            <TextInput
              style={styles.input}
              placeholder="Goal Title *"
              value={newGoal.title}
              onChangeText={text => setNewGoal({...newGoal, title: text})}
            />

            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Description *"
              value={newGoal.description}
              onChangeText={text =>
                setNewGoal({...newGoal, description: text})
              }
              multiline
              numberOfLines={3}
            />

            <View style={styles.modalButtons}>
              <TouchableOpacity
                style={[styles.modalButton, styles.cancelButton]}
                onPress={() => setShowAddGoal(false)}>
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalButton, styles.createButton]}
                onPress={handleCreateGoal}>
                <Text style={styles.createButtonText}>Create Goal</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
  },
  addButton: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#007AFF',
    borderRadius: 6,
  },
  addButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  section: {
    padding: 16,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#333',
    marginBottom: 16,
  },
  kpiCard: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  kpiHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  kpiName: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
  trendBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  trendText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  kpiValues: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 12,
  },
  kpiValueLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 4,
  },
  kpiValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  progressBarContainer: {
    height: 8,
    backgroundColor: '#e0e0e0',
    borderRadius: 4,
    marginBottom: 8,
    overflow: 'hidden',
  },
  progressBar: {
    height: '100%',
    backgroundColor: '#007AFF',
    borderRadius: 4,
  },
  percentageText: {
    fontSize: 12,
    color: '#666',
    textAlign: 'right',
  },
  goalCard: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  goalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  goalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
    flex: 1,
    marginRight: 8,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  goalDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 12,
  },
  goalMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  goalMetaText: {
    fontSize: 12,
    color: '#999',
  },
  modalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
  },
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 20,
    width: '90%',
    maxWidth: 400,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 20,
  },
  input: {
    backgroundColor: '#f5f5f5',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    fontSize: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  modalButtons: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 8,
  },
  modalButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  cancelButton: {
    backgroundColor: '#f5f5f5',
  },
  cancelButtonText: {
    color: '#666',
    fontSize: 16,
    fontWeight: '600',
  },
  createButton: {
    backgroundColor: '#007AFF',
  },
  createButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default GoalsScreen;
