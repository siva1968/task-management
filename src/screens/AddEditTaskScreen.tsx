import React, {useState} from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  Alert,
  Platform,
} from 'react-native';
import {Task, TaskPriority, TaskStatus, CreateTaskInput} from '../models/Task';
import TaskService from '../services/TaskService';

interface AddEditTaskScreenProps {
  task?: Task;
  onBack: () => void;
  onSave: () => void;
}

const AddEditTaskScreen: React.FC<AddEditTaskScreenProps> = ({
  task,
  onBack,
  onSave,
}) => {
  const [title, setTitle] = useState(task?.title || '');
  const [description, setDescription] = useState(task?.description || '');
  const [priority, setPriority] = useState<TaskPriority>(
    task?.priority || TaskPriority.MEDIUM,
  );
  const [status, setStatus] = useState<TaskStatus>(
    task?.status || TaskStatus.PENDING,
  );
  const [saving, setSaving] = useState(false);

  const isEdit = !!task;

  const handleSave = async () => {
    if (!title.trim()) {
      Alert.alert('Error', 'Please enter a task title');
      return;
    }

    if (!description.trim()) {
      Alert.alert('Error', 'Please enter a task description');
      return;
    }

    setSaving(true);
    try {
      if (isEdit) {
        await TaskService.updateTask({
          id: task.id,
          title,
          description,
          priority,
          status,
        });
        Alert.alert('Success', 'Task updated successfully');
      } else {
        const newTask: CreateTaskInput = {
          title,
          description,
          priority,
          status,
          tags: [],
        };
        await TaskService.createTask(newTask);
        Alert.alert('Success', 'Task created successfully');
      }
      onSave();
    } catch (error) {
      Alert.alert('Error', `Failed to ${isEdit ? 'update' : 'create'} task`);
    } finally {
      setSaving(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack}>
          <Text style={styles.backButton}>Cancel</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>
          {isEdit ? 'Edit Task' : 'New Task'}
        </Text>
        <TouchableOpacity onPress={handleSave} disabled={saving}>
          <Text
            style={[
              styles.saveButton,
              saving && styles.saveButtonDisabled,
            ]}>
            {saving ? 'Saving...' : 'Save'}
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.content}>
        <View style={styles.section}>
          <Text style={styles.label}>Title *</Text>
          <TextInput
            style={styles.input}
            value={title}
            onChangeText={setTitle}
            placeholder="Enter task title"
            editable={!saving}
          />
        </View>

        <View style={styles.section}>
          <Text style={styles.label}>Description *</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            value={description}
            onChangeText={setDescription}
            placeholder="Enter task description"
            multiline
            numberOfLines={4}
            textAlignVertical="top"
            editable={!saving}
          />
        </View>

        <View style={styles.section}>
          <Text style={styles.label}>Priority</Text>
          <View style={styles.optionsContainer}>
            {Object.values(TaskPriority).map(p => (
              <TouchableOpacity
                key={p}
                style={[
                  styles.option,
                  priority === p && styles.optionSelected,
                ]}
                onPress={() => setPriority(p)}
                disabled={saving}>
                <Text
                  style={[
                    styles.optionText,
                    priority === p && styles.optionTextSelected,
                  ]}>
                  {p.charAt(0).toUpperCase() + p.slice(1)}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.label}>Status</Text>
          <View style={styles.optionsContainer}>
            {Object.values(TaskStatus).map(s => (
              <TouchableOpacity
                key={s}
                style={[
                  styles.option,
                  status === s && styles.optionSelected,
                ]}
                onPress={() => setStatus(s)}
                disabled={saving}>
                <Text
                  style={[
                    styles.optionText,
                    status === s && styles.optionTextSelected,
                  ]}>
                  {s === TaskStatus.IN_PROGRESS
                    ? 'In Progress'
                    : s.charAt(0).toUpperCase() + s.slice(1)}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>
      </ScrollView>
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
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
  saveButton: {
    color: '#007AFF',
    fontSize: 16,
    fontWeight: '600',
  },
  saveButtonDisabled: {
    color: '#999',
  },
  content: {
    flex: 1,
    padding: 20,
  },
  section: {
    marginBottom: 24,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#fff',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  textArea: {
    height: 100,
    paddingTop: 12,
  },
  optionsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  option: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 8,
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  optionSelected: {
    backgroundColor: '#007AFF',
    borderColor: '#007AFF',
  },
  optionText: {
    fontSize: 14,
    color: '#666',
  },
  optionTextSelected: {
    color: '#fff',
    fontWeight: '600',
  },
});

export default AddEditTaskScreen;
