# Task Management Mobile App

A cross-platform mobile application for Android and iOS built with React Native and TypeScript. This app connects to the task management REST API for managing tasks, tracking progress, and team collaboration.

## Features

- 📱 **Cross-Platform**: Single codebase for Android and iOS
- 🔐 **JWT Authentication**: Secure login with token-based authentication
- ✅ **Task Management**: Create, update, delete, and view tasks
- 🎯 **Priority Levels**: Organize tasks by priority (Low, Medium, High, Urgent)
- 📊 **Status Tracking**: Track task status (Pending, In Progress, Completed, Archived)
- ⏱️ **Time Tracking**: Log time entries for tasks
- 📈 **Dashboard**: Overview with task statistics and team workload
- 🔔 **Notifications**: Real-time notifications with mark as read
- 📊 **Reports**: Task reports, time tracking, and team analytics
- 🎯 **Goals & KPIs**: Employee goals and performance indicators
- 🔄 **Real-time Sync**: Connected to REST API backend
- 📝 **TypeScript**: Full type safety throughout the application
- 🎨 **Bottom Tab Navigation**: Easy navigation between features

## Tech Stack

- **React Native** 0.73.2
- **TypeScript** 5.3.3
- **React** 18.2.0
- **AsyncStorage** for local token storage
- **REST API** integration with JWT authentication

## API Integration

The app connects to the following REST API:

**Base URL**: `https://tasks.getinstantleads.in/wp-json/sdm-mobile/v1/`

### Endpoints Used:

**Authentication:**
- `POST /auth/login` - User authentication
- `POST /auth/refresh` - Token refresh

**Tasks:**
- `GET /tasks` - Fetch all tasks
- `GET /tasks/{id}` - Get single task
- `POST /tasks` - Create new task
- `PUT /tasks/{id}` - Update task
- `DELETE /tasks/{id}` - Delete task
- `POST /tasks/{id}/status` - Update task status
- `POST /tasks/{id}/time` - Log time entry

**Dashboard:**
- `GET /dashboard/summary` - Get dashboard summary
- `GET /dashboard/team-workload` - Get team workload
- `GET /dashboard/trends` - Get trends data

**Notifications:**
- `GET /notifications` - Get notifications
- `POST /notifications/{id}/read` - Mark as read

**Reports:**
- `GET /reports/tasks` - Task reports
- `GET /reports/time-tracking` - Time tracking reports
- `GET /reports/client/{id}` - Client reports
- `GET /reports/team` - Team reports
- `POST /reports/export` - Export reports (PDF, CSV, Excel)
- `POST /reports/schedule` - Schedule reports

**Goals & Employees:**
- `GET /employees/{id}/kpis` - Get KPIs
- `GET /employees/{id}/goals` - Get goals
- `POST /goals` - Create goal

## Prerequisites

Before you begin, ensure you have the following installed:

### For All Platforms:
- [Node.js](https://nodejs.org/) (v18 or higher)
- [npm](https://www.npmjs.com/) or [Yarn](https://yarnpkg.com/)
- [Git](https://git-scm.com/)

### For Android Development:
- [Android Studio](https://developer.android.com/studio)
- Android SDK (API 34)
- Java Development Kit (JDK 11 or higher)
- Android Virtual Device (AVD) or physical Android device

### For iOS Development (macOS only):
- [Xcode](https://developer.apple.com/xcode/) (14 or higher)
- [CocoaPods](https://cocoapods.org/)
- iOS Simulator or physical iOS device

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/siva1968/task-management.git
   cd task-management
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Install iOS dependencies** (macOS only)
   ```bash
   cd ios
   pod install
   cd ..
   ```

## Running the App

### Start Metro Bundler

First, start the Metro bundler:

```bash
npm start
```

### Running on Android

Make sure you have an Android emulator running or a physical device connected via USB with USB debugging enabled.

```bash
npm run android
```

Or manually:
```bash
npx react-native run-android
```

### Running on iOS (macOS only)

Make sure you have Xcode installed and an iOS simulator set up.

```bash
npm run ios
```

Or manually:
```bash
npx react-native run-ios
```

To run on a specific iOS device:
```bash
npx react-native run-ios --device "Device Name"
```

## Project Structure

```
task-management/
├── android/                 # Android native code and configuration
│   ├── app/
│   │   ├── build.gradle
│   │   └── src/
│   │       └── main/
│   │           ├── AndroidManifest.xml
│   │           └── java/com/taskmanagement/
├── ios/                     # iOS native code and configuration
│   ├── TaskManagement/
│   │   ├── AppDelegate.h
│   │   ├── AppDelegate.mm
│   │   ├── Info.plist
│   │   └── main.m
│   └── Podfile
├── src/                     # Source code
│   ├── config/             # Configuration files
│   │   └── api.ts          # API endpoints and config
│   ├── models/             # TypeScript interfaces and types
│   │   └── Task.ts         # Task model and enums
│   ├── screens/            # App screens
│   │   ├── LoginScreen.tsx
│   │   ├── DashboardScreen.tsx
│   │   ├── TaskListScreen.tsx
│   │   ├── TaskDetailScreen.tsx
│   │   ├── AddEditTaskScreen.tsx
│   │   ├── NotificationsScreen.tsx
│   │   ├── ReportsScreen.tsx
│   │   └── GoalsScreen.tsx
│   └── services/           # Business logic and API calls
│       ├── ApiClient.ts          # HTTP client with auth
│       ├── AuthService.ts        # Authentication logic
│       ├── TaskService.ts        # Task CRUD operations
│       ├── DashboardService.ts   # Dashboard analytics
│       ├── NotificationService.ts# Notifications
│       ├── ReportService.ts      # Reports and exports
│       └── GoalService.ts        # Goals and KPIs
├── App.tsx                  # Main app component with navigation
├── index.js                 # App entry point
├── package.json            # Dependencies and scripts
├── tsconfig.json           # TypeScript configuration
└── README.md               # This file
```

## Configuration

### API Configuration

The API endpoints are configured in `src/config/api.ts`:

```typescript
export const API_CONFIG = {
  BASE_URL: 'https://tasks.getinstantleads.in/wp-json/sdm/v1',
  MOBILE_BASE_URL: 'https://tasks.getinstantleads.in/wp-json/sdm-mobile/v1',
  TIMEOUT: 30000,
};
```

To change the API endpoint, modify these values in `src/config/api.ts`.

## Building for Production

### Android APK

1. Generate a release APK:
   ```bash
   cd android
   ./gradlew assembleRelease
   ```

2. The APK will be generated at:
   ```
   android/app/build/outputs/apk/release/app-release.apk
   ```

### Android App Bundle (for Play Store)

```bash
cd android
./gradlew bundleRelease
```

The bundle will be at:
```
android/app/build/outputs/bundle/release/app-release.aab
```

### iOS

1. Open the project in Xcode:
   ```bash
   open ios/TaskManagement.xcworkspace
   ```

2. Select your target device or "Any iOS Device"

3. Go to **Product** → **Archive**

4. Follow the prompts to upload to App Store Connect

## Troubleshooting

### Android

**Issue**: App not installing
- Clear build cache: `cd android && ./gradlew clean`
- Restart adb: `adb kill-server && adb start-server`

**Issue**: Metro bundler connection refused
- Make sure Metro is running: `npm start`
- Clear Metro cache: `npm start -- --reset-cache`

### iOS

**Issue**: Pod install fails
- Update CocoaPods: `sudo gem install cocoapods`
- Clean and reinstall: `cd ios && pod deintegrate && pod install`

**Issue**: Build fails in Xcode
- Clean build folder: Cmd+Shift+K in Xcode
- Clear derived data: Xcode → Preferences → Locations → Derived Data

### General

**Issue**: TypeScript errors
- Check TypeScript version: `npx tsc --version`
- Run type check: `npm run type-check`

**Issue**: Module not found
- Clear node_modules: `rm -rf node_modules && npm install`
- Clear Metro cache: `npm start -- --reset-cache`

## Development

### Running Tests

```bash
npm test
```

### Type Checking

```bash
npm run type-check
```

### Linting

```bash
npm run lint
```

### Code Formatting

This project uses Prettier for code formatting:

```bash
npx prettier --write "src/**/*.{ts,tsx}"
```

## Authentication

The app uses JWT (JSON Web Token) authentication:

1. User enters credentials on login screen
2. App sends credentials to `/auth/login` endpoint
3. Server responds with JWT token and refresh token
4. Tokens are stored securely in AsyncStorage
5. All subsequent API requests include the JWT token
6. Token is automatically refreshed when expired

## Task Features

### Task Properties

- **Title**: Task name
- **Description**: Detailed description
- **Status**: Pending, In Progress, Completed, or Archived
- **Priority**: Low, Medium, High, or Urgent
- **Due Date**: Optional deadline
- **Tags**: Optional categorization
- **Timestamps**: Created and updated dates

### Task Operations

- ✅ View all tasks
- ➕ Create new task
- ✏️ Edit existing task
- 🗑️ Delete task
- 🔄 Change task status
- ⏱️ Log time entries
- 🔍 Search tasks
- 📋 Filter by status

## App Screens

### 1. Dashboard
- **Summary Statistics**: Total, completed, in progress, and overdue tasks
- **Team Workload**: View team members' assigned and completed tasks
- **Workload Percentage**: Visual progress bars for team capacity
- **Pull to Refresh**: Real-time data updates

### 2. Tasks
- **Task List**: Browse all tasks with status and priority badges
- **Task Details**: View complete task information
- **Time Tracking**: Log hours worked on tasks
- **Status Management**: Quick status updates
- **Add/Edit**: Full CRUD operations

### 3. Notifications
- **Notification Feed**: All system notifications
- **Unread Indicator**: Visual badges for unread items
- **Mark as Read**: Individual or bulk marking
- **Type-based Colors**: Info, Success, Warning, Error

### 4. Reports
- **Task Reports**: Comprehensive task analytics
- **Time Tracking Reports**: Hours logged by users
- **Team Reports**: Team productivity metrics
- **Export**: Download as PDF, CSV, or Excel
- **Scheduled Reports**: Automated report generation

### 5. Goals & KPIs
- **Key Performance Indicators**: Track progress vs targets
- **Goal Management**: Create and monitor goals
- **Progress Tracking**: Visual progress bars
- **Trend Indicators**: Up, Down, Stable trends
- **Goal Status**: Not Started, In Progress, Completed, Overdue

## Navigation

The app uses a **bottom tab navigation** with 5 main sections:

1. 📊 **Dashboard** - Overview and statistics
2. ✓ **Tasks** - Task management
3. 🔔 **Alerts** - Notifications
4. 📈 **Reports** - Analytics and exports
5. 🎯 **Goals** - KPIs and goals

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m 'feat: add my feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Submit a pull request

### Commit Message Format

Follow conventional commits format:

- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

## License

This project is part of the task management system by siva1968.

## Support

For issues and questions:
- Create an issue on GitHub
- Contact the development team

## Changelog

### Version 2.0.0 (2025-12-29)

- ✨ Complete feature set implementation
- 📊 Dashboard with statistics and team workload
- 🔔 Notifications system with real-time updates
- 📈 Reports with multiple export formats
- 🎯 Goals and KPIs tracking
- ⏱️ Time tracking for tasks
- 🎨 Bottom tab navigation
- 📱 22/22 API endpoints integrated (100% coverage)

### Version 1.0.0 (2025-12-29)

- ✨ Initial release
- 🔐 JWT authentication
- 📱 Android and iOS support
- ✅ Full task CRUD operations
- 🎯 Priority and status management
- 🔄 REST API integration
- 📱 8/22 API endpoints integrated

---

**Built with ❤️ using React Native**
