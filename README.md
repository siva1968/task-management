# Task Management Mobile App

A cross-platform mobile application for Android and iOS built with React Native and TypeScript. This app connects to the task management REST API for managing tasks, tracking progress, and team collaboration.

## Features

- 📱 **Cross-Platform**: Single codebase for Android and iOS
- 🔐 **JWT Authentication**: Secure login with token-based authentication
- ✅ **Task Management**: Create, update, delete, and view tasks
- 🎯 **Priority Levels**: Organize tasks by priority (Low, Medium, High, Urgent)
- 📊 **Status Tracking**: Track task status (Pending, In Progress, Completed, Archived)
- 🔄 **Real-time Sync**: Connected to REST API backend
- 📝 **TypeScript**: Full type safety throughout the application

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
- `POST /auth/login` - User authentication
- `POST /auth/refresh` - Token refresh
- `GET /tasks` - Fetch all tasks
- `GET /tasks/{id}` - Get single task
- `POST /tasks` - Create new task
- `PUT /tasks/{id}` - Update task
- `DELETE /tasks/{id}` - Delete task
- `POST /tasks/{id}/status` - Update task status

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
│   │   ├── TaskListScreen.tsx
│   │   ├── TaskDetailScreen.tsx
│   │   └── AddEditTaskScreen.tsx
│   └── services/           # Business logic and API calls
│       ├── ApiClient.ts    # HTTP client with auth
│       ├── AuthService.ts  # Authentication logic
│       └── TaskService.ts  # Task CRUD operations
├── App.tsx                  # Main app component
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
- 🔍 Search tasks
- 📋 Filter by status

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

### Version 1.0.0 (2025-12-29)

- ✨ Initial release
- 🔐 JWT authentication
- 📱 Android and iOS support
- ✅ Full task CRUD operations
- 🎯 Priority and status management
- 🔄 REST API integration

---

**Built with ❤️ using React Native**
