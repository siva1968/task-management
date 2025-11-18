# CLAUDE.md - AI Assistant Guide for Task Management System

## Repository Overview

This is a **task-management** system repository. The codebase is designed to help users organize, track, and manage tasks efficiently.

**Repository:** siva1968/task-management
**Last Updated:** 2025-11-18

---

## Codebase Structure

### Expected Directory Layout

```
task-management/
├── src/                    # Source code
│   ├── components/         # UI components (if applicable)
│   ├── services/          # Business logic and services
│   ├── models/            # Data models and types
│   ├── utils/             # Utility functions
│   └── api/               # API endpoints and clients
├── tests/                 # Test files
├── docs/                  # Documentation
├── config/                # Configuration files
├── scripts/               # Build and utility scripts
└── public/                # Static assets (if web-based)
```

### Key Files to Look For

- `package.json` / `requirements.txt` / `Cargo.toml` - Dependencies and project metadata
- `README.md` - Project documentation and setup instructions
- `.env.example` - Environment variable templates
- Configuration files (`.eslintrc`, `tsconfig.json`, etc.)

---

## Development Workflows

### 1. Making Changes

**Before Starting:**
- Read existing code to understand patterns and conventions
- Check for related tests that may need updates
- Look for similar implementations to maintain consistency

**During Development:**
- Follow existing code style and naming conventions
- Write tests for new functionality
- Update documentation when adding features
- Keep commits focused and atomic

**After Changes:**
- Run tests to ensure nothing breaks
- Check for linting errors
- Update relevant documentation
- Verify the build succeeds

### 2. Testing

- Look for test files in `tests/`, `__tests__/`, or `*.test.*` / `*.spec.*` files
- Run the test suite before committing changes
- Add tests for new features and bug fixes
- Maintain test coverage for critical paths

### 3. Committing Code

**Commit Message Format:**
```
<type>: <short summary>

<optional detailed description>
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

**Examples:**
- `feat: add task priority levels`
- `fix: resolve date parsing issue in task creation`
- `docs: update API documentation for task endpoints`

### 4. Branch Strategy

- **Feature branches:** `claude/<session-id>` (for AI-driven development)
- **Main branch:** Production-ready code
- Always develop on designated feature branches
- Never push directly to main without approval

---

## Key Conventions

### Code Style

1. **Naming Conventions:**
   - Use descriptive, meaningful names
   - Functions/methods: `camelCase` or `snake_case` (depending on language)
   - Classes: `PascalCase`
   - Constants: `UPPER_SNAKE_CASE`
   - Files: Match the primary export or follow project convention

2. **File Organization:**
   - One primary component/class per file
   - Group related functionality together
   - Keep files focused and manageable (<500 lines typically)

3. **Comments and Documentation:**
   - Write self-documenting code when possible
   - Add comments for complex logic or non-obvious decisions
   - Include JSDoc/docstrings for public APIs
   - Keep comments up-to-date with code changes

### Task Management Specific Patterns

When working with task-related features, consider:

1. **Task Properties:**
   - ID/unique identifier
   - Title/description
   - Status (pending, in_progress, completed, etc.)
   - Priority (low, medium, high, urgent)
   - Due date/deadline
   - Created/updated timestamps
   - Assignee/owner
   - Tags/categories

2. **Common Operations:**
   - Create task
   - Update task (status, details, etc.)
   - Delete task
   - List/filter tasks
   - Search tasks
   - Sort tasks (by date, priority, status)
   - Archive/restore tasks

3. **Data Validation:**
   - Validate required fields
   - Sanitize user input
   - Check date ranges
   - Prevent duplicate tasks (when appropriate)

---

## AI Assistant Guidelines

### When Exploring Code

1. **Start with high-level overview:**
   - Check README and documentation first
   - Review package.json/dependencies to understand tech stack
   - Look at directory structure

2. **Use appropriate tools:**
   - `Glob` for finding files by pattern
   - `Grep` for searching code content
   - `Read` for examining specific files
   - `Task` tool with `Explore` subagent for complex codebase exploration

3. **Understand before modifying:**
   - Read existing implementations
   - Check for tests
   - Look for similar patterns elsewhere in the codebase

### When Writing Code

1. **Follow existing patterns:**
   - Match the coding style already present
   - Use the same libraries/frameworks for similar tasks
   - Follow established error handling patterns
   - Maintain consistency with existing architecture

2. **Security considerations:**
   - Validate and sanitize all inputs
   - Avoid SQL injection vulnerabilities
   - Prevent XSS attacks in web interfaces
   - Use parameterized queries
   - Handle authentication/authorization properly
   - Don't expose sensitive information in logs or errors

3. **Error handling:**
   - Handle edge cases gracefully
   - Provide meaningful error messages
   - Don't swallow exceptions silently
   - Log errors appropriately

4. **Performance:**
   - Avoid N+1 queries
   - Use appropriate data structures
   - Consider pagination for large datasets
   - Cache when appropriate

### When Making Changes

1. **Use TodoWrite tool:**
   - Break down complex tasks into steps
   - Track progress through implementation
   - Mark items complete as you finish them
   - Keep user informed of progress

2. **Testing:**
   - Run existing tests before and after changes
   - Add tests for new functionality
   - Fix any broken tests
   - Verify edge cases

3. **Documentation:**
   - Update README if adding features
   - Add code comments for complex logic
   - Update API documentation
   - Keep CLAUDE.md current

### Communication

1. **Be concise:**
   - Provide clear, focused responses
   - Use code references with `file:line` format
   - Avoid unnecessary verbosity

2. **Show progress:**
   - Use TodoWrite to track multi-step tasks
   - Update status as work progresses
   - Inform user of completions

3. **Ask when uncertain:**
   - Clarify requirements when ambiguous
   - Confirm before making destructive changes
   - Verify assumptions with the user

---

## Common Tasks Reference

### Setting Up Development Environment

```bash
# Clone the repository
git clone <repo-url>
cd task-management

# Install dependencies (adjust based on tech stack)
npm install        # Node.js
pip install -r requirements.txt  # Python
cargo build        # Rust

# Run tests
npm test           # Node.js
pytest             # Python
cargo test         # Rust

# Start development server
npm run dev        # Node.js
python manage.py runserver  # Django
cargo run          # Rust
```

### Running Tests

Always run tests before committing:
```bash
# Run all tests
npm test / pytest / cargo test

# Run specific test file
npm test -- path/to/test
pytest path/to/test
cargo test test_name

# Run with coverage
npm run test:coverage
pytest --cov
cargo tarpaulin
```

### Building for Production

```bash
# Build the project
npm run build      # Node.js
python setup.py build  # Python
cargo build --release  # Rust
```

---

## Technology Stack

*This section will be updated as the tech stack is determined.*

### Current Stack:
- To be determined based on initial implementation

### Potential Technologies:
- **Frontend:** React, Vue, Angular, Svelte
- **Backend:** Node.js, Python (Django/Flask), Rust, Go
- **Database:** PostgreSQL, MySQL, MongoDB, SQLite
- **Testing:** Jest, Pytest, Cargo test
- **Build Tools:** Webpack, Vite, Rollup

---

## Resources

### Documentation
- Project README: `README.md`
- API Documentation: `docs/api.md` (if applicable)
- Architecture: `docs/architecture.md` (if applicable)

### External Resources
- [Git Commit Message Guidelines](https://www.conventionalcommits.org/)
- [Code Review Best Practices](https://google.github.io/eng-practices/review/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/) - Security vulnerabilities to avoid

---

## Maintenance

### Updating This Document

This CLAUDE.md file should be updated when:
- Project structure changes significantly
- New conventions are established
- Tech stack is modified
- New workflows are introduced
- Important patterns emerge

### Version History

- **2025-11-18:** Initial creation - Repository setup and template structure

---

## Quick Reference

### Essential Commands

```bash
# Check current branch
git status

# Run tests
[test-command-for-your-stack]

# Commit changes
git add .
git commit -m "feat: description"

# Push to feature branch
git push -u origin claude/<session-id>
```

### File Patterns to Search

- Task models: `**/models/*task*` or `**/types/*task*`
- Task services: `**/services/*task*`
- Task components: `**/components/*task*`
- Task tests: `**/*task*.test.*` or `**/*task*.spec.*`

### Common Search Patterns

```bash
# Find task creation logic
grep -r "createTask\|create_task" src/

# Find task status handling
grep -r "status.*task\|task.*status" src/

# Find database models
grep -r "class.*Task\|interface Task" src/
```

---

## Notes for AI Assistants

1. **Always read before writing:** Use `Read` to examine existing files before making changes
2. **Search strategically:** Use `Glob` for file patterns, `Grep` for content, `Task/Explore` for complex investigations
3. **Track your work:** Use `TodoWrite` for multi-step tasks
4. **Test thoroughly:** Run tests before committing
5. **Follow conventions:** Match existing code style and patterns
6. **Communicate clearly:** Keep responses concise and informative
7. **Ask when unsure:** Clarify requirements rather than guessing
8. **Security first:** Always consider security implications of code changes
9. **Document changes:** Update relevant documentation when adding features
10. **Commit wisely:** Write clear commit messages and commit logical units of work

---

*This document is maintained by AI assistants working on this repository. Keep it updated as the project evolves.*
