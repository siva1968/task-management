import React, {useState, useEffect} from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  Alert,
  Linking,
} from 'react-native';
import ReportService, {
  TaskReport,
  TimeTrackingReport,
  TeamReport,
  ExportOptions,
} from '../services/ReportService';

type ReportType = 'tasks' | 'time-tracking' | 'team';

const ReportsScreen: React.FC = () => {
  const [selectedReport, setSelectedReport] = useState<ReportType>('tasks');
  const [taskReports, setTaskReports] = useState<TaskReport[]>([]);
  const [timeReports, setTimeReports] = useState<TimeTrackingReport[]>([]);
  const [teamReports, setTeamReports] = useState<TeamReport[]>([]);
  const [loading, setLoading] = useState(false);
  const [exporting, setExporting] = useState(false);

  useEffect(() => {
    loadReport(selectedReport);
  }, [selectedReport]);

  const loadReport = async (type: ReportType) => {
    setLoading(true);
    try {
      switch (type) {
        case 'tasks':
          const tasks = await ReportService.getTaskReports();
          setTaskReports(tasks);
          break;
        case 'time-tracking':
          const time = await ReportService.getTimeTrackingReports();
          setTimeReports(time);
          break;
        case 'team':
          const team = await ReportService.getTeamReports();
          setTeamReports(team);
          break;
      }
    } catch (error: any) {
      Alert.alert('Error', 'Failed to load report');
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async (format: 'pdf' | 'csv' | 'excel') => {
    setExporting(true);
    try {
      const options: ExportOptions = {
        format,
        reportType: selectedReport,
      };
      const result = await ReportService.exportReport(options);
      Alert.alert(
        'Export Successful',
        'Report exported successfully. Download link generated.',
        [
          {text: 'Cancel', style: 'cancel'},
          {
            text: 'Open',
            onPress: () => Linking.openURL(result.url),
          },
        ],
      );
    } catch (error) {
      Alert.alert('Error', 'Failed to export report');
    } finally {
      setExporting(false);
    }
  };

  const renderTaskReports = () => (
    <View>
      {taskReports.map((report, index) => (
        <View key={index} style={styles.reportCard}>
          <Text style={styles.reportTitle}>{report.taskTitle}</Text>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Status:</Text>
            <Text style={styles.reportValue}>{report.status}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Priority:</Text>
            <Text style={styles.reportValue}>{report.priority}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Assignee:</Text>
            <Text style={styles.reportValue}>{report.assignee}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Time Spent:</Text>
            <Text style={styles.reportValue}>{report.timeSpent}h</Text>
          </View>
        </View>
      ))}
    </View>
  );

  const renderTimeReports = () => (
    <View>
      {timeReports.map((report, index) => (
        <View key={index} style={styles.reportCard}>
          <Text style={styles.reportTitle}>{report.taskTitle}</Text>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>User:</Text>
            <Text style={styles.reportValue}>{report.userName}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Hours:</Text>
            <Text style={styles.reportValue}>{report.hours}h</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Date:</Text>
            <Text style={styles.reportValue}>
              {report.date.toLocaleDateString()}
            </Text>
          </View>
          {report.description && (
            <View style={styles.reportRow}>
              <Text style={styles.reportLabel}>Description:</Text>
              <Text style={styles.reportValue}>{report.description}</Text>
            </View>
          )}
        </View>
      ))}
    </View>
  );

  const renderTeamReports = () => (
    <View>
      {teamReports.map((report, index) => (
        <View key={index} style={styles.reportCard}>
          <Text style={styles.reportTitle}>{report.teamMemberName}</Text>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Completed:</Text>
            <Text style={styles.reportValue}>{report.tasksCompleted}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>In Progress:</Text>
            <Text style={styles.reportValue}>{report.tasksInProgress}</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Total Hours:</Text>
            <Text style={styles.reportValue}>{report.totalHours}h</Text>
          </View>
          <View style={styles.reportRow}>
            <Text style={styles.reportLabel}>Productivity:</Text>
            <Text style={styles.reportValue}>{report.productivity}%</Text>
          </View>
        </View>
      ))}
    </View>
  );

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Reports</Text>
      </View>

      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[
            styles.tab,
            selectedReport === 'tasks' && styles.activeTab,
          ]}
          onPress={() => setSelectedReport('tasks')}>
          <Text
            style={[
              styles.tabText,
              selectedReport === 'tasks' && styles.activeTabText,
            ]}>
            Tasks
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[
            styles.tab,
            selectedReport === 'time-tracking' && styles.activeTab,
          ]}
          onPress={() => setSelectedReport('time-tracking')}>
          <Text
            style={[
              styles.tabText,
              selectedReport === 'time-tracking' && styles.activeTabText,
            ]}>
            Time
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[
            styles.tab,
            selectedReport === 'team' && styles.activeTab,
          ]}
          onPress={() => setSelectedReport('team')}>
          <Text
            style={[
              styles.tabText,
              selectedReport === 'team' && styles.activeTabText,
            ]}>
            Team
          </Text>
        </TouchableOpacity>
      </View>

      <View style={styles.exportContainer}>
        <Text style={styles.exportLabel}>Export as:</Text>
        <TouchableOpacity
          style={styles.exportButton}
          onPress={() => handleExport('pdf')}
          disabled={exporting}>
          <Text style={styles.exportButtonText}>PDF</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={styles.exportButton}
          onPress={() => handleExport('csv')}
          disabled={exporting}>
          <Text style={styles.exportButtonText}>CSV</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={styles.exportButton}
          onPress={() => handleExport('excel')}
          disabled={exporting}>
          <Text style={styles.exportButtonText}>Excel</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#007AFF" />
        </View>
      ) : (
        <ScrollView style={styles.content}>
          {selectedReport === 'tasks' && renderTaskReports()}
          {selectedReport === 'time-tracking' && renderTimeReports()}
          {selectedReport === 'team' && renderTeamReports()}
        </ScrollView>
      )}
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
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  tab: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  activeTab: {
    borderBottomColor: '#007AFF',
  },
  tabText: {
    fontSize: 16,
    color: '#666',
  },
  activeTabText: {
    color: '#007AFF',
    fontWeight: '600',
  },
  exportContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  exportLabel: {
    fontSize: 14,
    color: '#666',
    marginRight: 12,
  },
  exportButton: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#007AFF',
    borderRadius: 6,
    marginRight: 8,
  },
  exportButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  content: {
    flex: 1,
    padding: 16,
  },
  reportCard: {
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
  reportTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
    marginBottom: 12,
  },
  reportRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  reportLabel: {
    fontSize: 14,
    color: '#666',
  },
  reportValue: {
    fontSize: 14,
    color: '#333',
    fontWeight: '500',
  },
});

export default ReportsScreen;
