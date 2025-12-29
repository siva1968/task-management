import React, {useState, useEffect, useCallback} from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  ActivityIndicator,
  RefreshControl,
  Alert,
} from 'react-native';
import DashboardService, {
  DashboardSummary,
  TeamMember,
} from '../services/DashboardService';

const DashboardScreen: React.FC = () => {
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [teamWorkload, setTeamWorkload] = useState<TeamMember[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadDashboardData = useCallback(async () => {
    try {
      const [summaryData, workloadData] = await Promise.all([
        DashboardService.getSummary(),
        DashboardService.getTeamWorkload(),
      ]);
      setSummary(summaryData);
      setTeamWorkload(workloadData);
    } catch (error: any) {
      Alert.alert('Error', 'Failed to load dashboard data');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    loadDashboardData();
  }, [loadDashboardData]);

  const onRefresh = () => {
    setRefreshing(true);
    loadDashboardData();
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#007AFF" />
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Dashboard</Text>
      </View>

      {summary && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Summary</Text>
          <View style={styles.statsGrid}>
            <View style={[styles.statCard, {backgroundColor: '#007AFF'}]}>
              <Text style={styles.statValue}>{summary.totalTasks}</Text>
              <Text style={styles.statLabel}>Total Tasks</Text>
            </View>
            <View style={[styles.statCard, {backgroundColor: '#34C759'}]}>
              <Text style={styles.statValue}>{summary.completedTasks}</Text>
              <Text style={styles.statLabel}>Completed</Text>
            </View>
            <View style={[styles.statCard, {backgroundColor: '#FF9500'}]}>
              <Text style={styles.statValue}>{summary.inProgressTasks}</Text>
              <Text style={styles.statLabel}>In Progress</Text>
            </View>
            <View style={[styles.statCard, {backgroundColor: '#FF3B30'}]}>
              <Text style={styles.statValue}>{summary.overdueTasks}</Text>
              <Text style={styles.statLabel}>Overdue</Text>
            </View>
          </View>
        </View>
      )}

      {teamWorkload.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Team Workload</Text>
          {teamWorkload.map(member => (
            <View key={member.id} style={styles.teamMemberCard}>
              <View style={styles.teamMemberInfo}>
                <View style={styles.avatar}>
                  <Text style={styles.avatarText}>
                    {member.name
                      .split(' ')
                      .map(n => n[0])
                      .join('')
                      .toUpperCase()}
                  </Text>
                </View>
                <View style={styles.memberDetails}>
                  <Text style={styles.memberName}>{member.name}</Text>
                  <Text style={styles.memberEmail}>{member.email}</Text>
                </View>
              </View>
              <View style={styles.workloadStats}>
                <View style={styles.workloadStat}>
                  <Text style={styles.workloadValue}>
                    {member.assignedTasks}
                  </Text>
                  <Text style={styles.workloadLabel}>Assigned</Text>
                </View>
                <View style={styles.workloadStat}>
                  <Text style={styles.workloadValue}>
                    {member.completedTasks}
                  </Text>
                  <Text style={styles.workloadLabel}>Completed</Text>
                </View>
              </View>
              <View style={styles.progressBarContainer}>
                <View
                  style={[
                    styles.progressBar,
                    {width: `${member.workloadPercentage}%`},
                  ]}
                />
              </View>
              <Text style={styles.workloadPercentage}>
                {member.workloadPercentage}% Workload
              </Text>
            </View>
          ))}
        </View>
      )}
    </ScrollView>
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
  section: {
    padding: 16,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#333',
    marginBottom: 16,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  statCard: {
    flex: 1,
    minWidth: '45%',
    padding: 20,
    borderRadius: 12,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
  },
  teamMemberCard: {
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
  teamMemberInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#007AFF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  avatarText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  memberDetails: {
    flex: 1,
  },
  memberName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  memberEmail: {
    fontSize: 14,
    color: '#666',
  },
  workloadStats: {
    flexDirection: 'row',
    gap: 16,
    marginBottom: 12,
  },
  workloadStat: {
    alignItems: 'center',
  },
  workloadValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  workloadLabel: {
    fontSize: 12,
    color: '#999',
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
  workloadPercentage: {
    fontSize: 12,
    color: '#666',
    textAlign: 'right',
  },
});

export default DashboardScreen;
