# Digital Tolk Assesment



## Improvements Made in BookingController:

**Roles and Permissions:** Replaced use of env variables with Laravel Spatie package for handling roles and permissions.

**Array Handling:** Switched from array_except to `$request->except()` for cleaner code.

**Error Handling:** Added error handling for missing data and edge cases.

**Request Validation:** Implemented validation for request data to ensure data integrity.

**Optimized Code:** Refactored code to eliminate unnecessary if-else statements and improve readability.

**Error and Success Responses:** Handled error and success cases more effectively for better user feedback.



## Improvements Made in BookingRepository:

1. **Introduced Services Layer:**
    - Implemented a service layer for better modularity and separation of concerns.
    - Added the following services:
        - NotificationService
        - JobService
        - BookingService
        - ThrottleService
        - TranslatorService

2. **Removed Tight Coupling with Logger:**
    - Decoupled the logger from the BookingRepository, improving flexibility and maintainability.

3. **Corrected Inconsistent Naming:**
    - Fixed a typo in variable naming, changing `$normalJobs` to `$normalJobs`.

4. **Optimized Redundant Code:**
    - Streamlined conditional checks, converting repetitive if conditions involving `$cuser` into a more concise ternary if-else structure.

5. **Removed Error Suppression (@):**
    - Eliminated the use of the error suppression operator (@). Now, variables are properly validated before usage to prevent hidden bugs.

6. **Refactored Large Functions (store(), getAll(), getUsersJobs(), etc.):**
    - Applied the Single Responsibility Principle (SRP) by breaking down large functions into smaller, focused ones.
    - Removed duplicate code and optimized overall functionality for better performance and readability.

**Except above there are more Improvements that Can make it good Code**

**1. By Using Enums Instead of String Comparisons**

**Why:** Enums provide a way to define a set of named values. This makes code more readable and less error-prone compared to using plain strings.

**How:** Define enums for status, types, and other fixed sets of values. For example, replace status strings with an enum:

```php
enum JobStatus: string {
    case WITHDRAW_BEFORE_24 = 'withdrawbefore24';
    case WITHDRAW_AFTER_24 = 'withdrawafter24';
    case PENDING = 'pending';
}
```

**2. Single Responsibility Principle**

**Why:** Functions should do one thing and do it well. This makes code easier to understand, test, and maintain.

**How:** Break down large functions into smaller, more focused methods. Each method should handle a specific task.
Example: Instead of having one method handle logging, status updates, and notifications, separate these concerns:

```php
public function cancelJob($job, $user) {
$this->updateJobStatus($job);
$this->handleNotifications($job, $user);
}

private function updateJobStatus($job) {

}

private function handleNotifications($job, $user) {

}
```


**3. Reduce Conditional Logic**

**Why:** Excessive if-else statements can make code hard to read and maintain.

**How:** Use polymorphism or strategy patterns to handle different conditions more elegantly. For example, instead of checking conditions in a single method, use different methods or classes for different scenarios.
Example: Instead of multiple if-else statements to check the user type, use a strategy pattern:

```php
interface UserTypeHandler {
public function handle($job);
}

class CustomerHandler implements UserTypeHandler {
public function handle($job) {
// customer logic
}
}

class TranslatorHandler implements UserTypeHandler {
public function handle($job) {
//  transaltor logic
}
}

// In the main method
$handler = $this->getHandlerForUserType($userType);
$handler->handle($job);
```


**5. Improve Logging and Error Handling**

**Why:** Proper logging and error handling are crucial for debugging and maintaining applications.

**How:** Implement a centralized logging mechanism. Use appropriate log levels and ensure that all significant actions and errors are logged. Consider using Laravel’s built-in logging and error handling features.

**6. Use Dependency Injection**

**Why:** It makes your code more testable and follows the Dependency Inversion Principle.

**How:** Inject dependencies into classes instead of creating them within the class. This promotes loose coupling and makes unit testing easier.

**7. Improve Code Readability**

**Why:** Readable code is easier to understand and maintain.

**How:** Use meaningful variable and method names. Write clear and concise comments where necessary. Follow coding standards and conventions.

**8.  Simplify and Optimize Queries**
```
Use Eloquent Relationships: Leverage Laravel’s Eloquent relationships to simplify queries and reduce the need for complex joins.

// Instead of manual joins
$jobs = Job::join('users', 'jobs.user_id', '=', 'users.id')
->select('jobs.*', 'users.name')
->get();

// Use Eloquent relationships
$jobs = Job::with('user')->get();
Select Only Necessary Columns: Avoid using select('*') and fetch only the columns you need.
// Fetch only required columns
$jobs = Job::select('id', 'status', 'due')->get();

Paginate Results: Use pagination for large datasets to avoid loading everything into memory.

// Paginate results
$jobs = Job::paginate(10);

Index Important Columns: Ensure that columns used in WHERE, JOIN, and ORDER BY clauses are indexed.
Use Caching: Cache results of expensive operations to reduce the need for repeated computations or database queries.

// Cache job details
$job = Cache::remember("job_{$jobId}", 60, function () use ($jobId) {
return Job::findOrFail($jobId);
});
```
## Summary

By introducing a service layer, using enums, adhering to the Single Responsibility Principle, reducing conditional logic, breaking down large functions, improving logging and error handling, using dependency injection, optimizing performance, and following SOLID principles, we can significantly enhance the quality and maintainability of your code. To optimize the code, we need to focus on efficient query design, proper error handling, and effective notification management. By simplifying and optimizing queries, business logic, and error handling, we can improve performance, maintainability, and readability. Implementing these practices will help in creating a more robust and efficient application.