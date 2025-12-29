import React, {useState} from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  Alert,
  TextInput,
  Modal,
} from 'react-native';
import {Task, TaskStatus, TaskPriority} from '../models/Task';
import TaskService from '../services/TaskService';

interface TaskDetailScreenProps {
  task: Task;
  onBack: () => void;
  onEdit: (task: Task) => void;
  onDelete: () => void;
}

const TaskDetailScreen: React.FC<TaskDetailScreenProps> = ({
  task,
  onBack,
  onEdit,
  onDelete,
}) => {
  const [currentTask, setCurrentTask] = useState<Task>(task);
  const [showTimeModal, setShowTimeModal] = useState(false);
  const [timeHours, setTimeHours] = useState('');
  const [timeDescription, setTimeDescription] = useState('');

  const getPriorityColor = (priority: TaskPriority): string => {
    switch (priority) {
      case TaskPriority.URGENT:
        return '#ff3b30';
      case TaskPriority.HIGH:
        return '#ff9500';
      case TaskPriority.MEDIUM:
        return '#ffcc00';
      case TaskPriority.LOW:
        return '#34c759';
      default:
        return '#999';
    }
  };

  const getStatusLabel = (status: TaskStatus): string => {
    switch (status) {
      case TaskStatus.PENDING:
        return 'Pending';
      case TaskStatus.IN_PROGRESS:
        return 'In Progress';
      case TaskStatus.COMPLETED:
        return 'Completed';
      case TaskStatus.ARCHIVED:
        return 'Archived';
      default:
        return status;
    }
  };

  const handleStatusChange = async (newStatus: TaskStatus) => {
    try {
      const updated = await TaskService.updateTaskStatus(currentTask.id, newStatus);
      if (updated) {
        setCurrentTask(updated);
        Alert.alert('Success', 'Task status updated');
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to update task status');
    }
  };

  const handleDelete = () => {
    Alert.alert('Delete Task', 'Are you sure you want to delete this task?', [
      {text: 'Cancel', style: 'cancel'},
      {
        text: 'Delete',
        style: 'destructive',
        onPress: async () => {
          const success = await TaskService.deleteTask(currentTask.id);
          if (success) {
            onDelete();
          } else {
            Alert.alert('Error', 'Failed to delete task');
          }
        },
      },
    ]);
  };

  const handleLogTime = async () => {
    const hours = parseFloat(timeHours);
    if (isNaN(hours) || hours <= 0) {
      Alert.alert('Error', 'Please enter valid hours');
      return;
    }

    try {
      await TaskService.logTime(currentTask.id, {
        hours,
        description: timeDescription,
      });
      setShowTimeModal(false);
      setTimeHours('');
      setTimeDescription('');
      Alert.alert('Success', 'Time logged successfully');
    } catch (error) {
      Alert.alert('Error', 'Failed to log time');
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack}>
          <Text style={styles.backButton}>← Back</Text>
        </TouchableOpacity>
        <View style={styles.headerActions}>
          <TouchableOpacity
            style={styles.editButton}
            onPress={() => onEdit(currentTask)}>
            <Text style={styles.editButtonText}>Edit</Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={handleDelete}>
            <Text style={styles.deleteButton}>Delete</Text>
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView style={styles.content}>
        <View style={styles.section}>
          <Text style={styles.title}>{currentTask.title}</Text>
          <View style={styles.badges}>
            <View
              style={[
                styles.priorityBadge,
                {backgroundColor: getPriorityColor(currentTask.priority)},
              ]}>
              <Text style={styles.badgeText}>{currentTask.priority}</Text>
            </View>
            <View style={styles.statusBadge}>
              <Text style={styles.statusBadgeText}>
                {getStatusLabel(currentTask.status)}
              </Text>
            </View>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Description</Text>
          <Text style={styles.description}>{currentTask.description}</Text>
        </View>

        {currentTask.dueDate && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Due Date</Text>
            <Text style={styles.dateText}>
              {currentTask.dueDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
              })}
            </Text>
          </View>
        )}

        {currentTask.tags && currentTask.tags.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Tags</Text>
            <View style={styles.tagsContainer}>
              {currentTask.tags.map((tag, index) => (
                <View key={index} style={styles.tag}>
                  <Text style={styles.tagText}>{tag}</Text>
                </View>
              ))}
            </View>
          </View>
        )}

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Change Status</Text>
          <View style={styles.statusButtons}>
            <TouchableOpacity
              style={[
                styles.statusButton,
                currentTask.status === TaskStatus.PENDING &&
                  styles.statusButtonActive,
              ]}
              onPress={() => handleStatusChange(TaskStatus.PENDING)}>
              <Text style={styles.statusButtonText}>Pending</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[
                styles.statusButton,
                currentTask.status === TaskStatus.IN_PROGRESS &&
                  styles.statusButtonActive,
              ]}
              onPress={() => handleStatusChange(TaskStatus.IN_PROGRESS)}>
              <Text style={styles.statusButtonText}>In Progress</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[
                styles.statusButton,
                currentTask.status === TaskStatus.COMPLETED &&
                  styles.statusButtonActive,
              ]}
              onPress={() => handleStatusChange(TaskStatus.COMPLETED)}>
              <Text style={styles.statusButtonText}>Completed</Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Time Tracking</Text>
          <TouchableOpacity
            style={styles.logTimeButton}
            onPress={() => setShowTimeModal(true)}>
            <Text style={styles.logTimeButtonText}>+ Log Time</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Timestamps</Text>
          <Text style={styles.timestampText}>
            Created: {currentTask.createdAt.toLocaleString()}
          </Text>
          <Text style={styles.timestampText}>
            Updated: {currentTask.updatedAt.toLocaleString()}
          </Text>
        </View>
      </ScrollView>

      <Modal
        visible={showTimeModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowTimeModal(false)}>
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Log Time</Text>

            <TextInput
              style={styles.input}
              placeholder="Hours *"
              value={timeHours}
              onChangeText={setTimeHours}
              keyboardType="decimal-pad"
            />

            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Description (optional)"
              value={timeDescription}
              onChangeText={setTimeDescription}
              multiline
              numberOfLines={3}
            />

            <View style={styles.modalButtons}>
              <TouchableOpacity
                style={[styles.modalButton, styles.cancelButton]}
                onPress={() => setShowTimeModal(false)}>
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalButton, styles.logButton]}
                onPress={handleLogTime}>
                <Text style={styles.logButtonText}>Log Time</Text>
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
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  backButton: {
    color: '#007AFF',
    fontSize: 16,
  },
  headerActions: {
    flexDirection: 'row',
    gap: 16,
  },
  editButton: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    backgroundColor: '#007AFF',
    borderRadius: 6,
  },
  editButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  deleteButton: {
    color: '#ff3b30',
    fontSize: 16,
  },
  content: {
    flex: 1,
    padding: 20,
  },
  section: {
    marginBottom: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  badges: {
    flexDirection: 'row',
    gap: 8,
  },
  priorityBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  badgeText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
    backgroundColor: '#007AFF',
  },
  statusBadgeText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  description: {
    fontSize: 16,
    color: '#666',
    lineHeight: 24,
  },
  dateText: {
    fontSize: 16,
    color: '#666',
  },
  tagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  tag: {
    backgroundColor: '#e0e0e0',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  tagText: {
    fontSize: 14,
    color: '#666',
  },
  statusButtons: {
    flexDirection: 'row',
    gap: 8,
  },
  statusButton: {
    flex: 1,
    paddingVertical: 12,
    backgroundColor: '#fff',
    borderRadius: 8,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  statusButtonActive: {
    backgroundColor: '#007AFF',
    borderColor: '#007AFF',
  },
  statusButtonText: {
    fontSize: 14,
    color: '#666',
    fontWeight: '500',
  },
  timestampText: {
    fontSize: 14,
    color: '#999',
    marginBottom: 4,
  },
  logTimeButton: {
    backgroundColor: '#34C759',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  logTimeButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
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
  logButton: {
    backgroundColor: '#34C759',
  },
  logButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default TaskDetailScreen;
