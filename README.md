# Research Submission and Evaluation System
*A Web-Based Platform for Managing University Research Paper Workflows.*  
### Overview  
The Research Submission & Evaluation System is a web-based platform designed to streamline the complete lifecycle of university research paper management — from submission to evaluation, feedback, and publication.

The system replaces traditional paper-based workflows with a structured, transparent, and efficient digital process. It enables students to submit research papers, supervisors to evaluate them, and administrators to monitor progress and generate reports. A centralized repository stores all accepted and published research papers for easy access.
### Key Features
1. Streamlined Research Submission
   - Students submit research papers in PDF or MS Word format.
   - Metadata collection includes: Student ID, Study Program, Paper Title, Abstract, Keywords, and Category/Domain.
   - Students select their research assignment from an available list.
   - Deadline-based submission control.
2. Evaluation & Feedback Workflow
   - Supervisors evaluate papers using predefined criteria (clarity, originality, structure, novelty, etc.).
   - Status options include: Accepted, Rejected, Needs Improvement, Accepted & Published.
   - Feedback, comments, and annotated files can be uploaded.
3. Notifications & Communication
   - Email alerts for submission, evaluation, feedback, and pending actions.
   - Supervisors receive notifications for new submissions.
   - Admins receive alerts for pending evaluations or system issues.
4. Transparency & Tracking
   - Students can track submission status in real time.
   - Version history is maintained for updated submissions.
   - Supervisors can view past evaluations and submissions.
  
5. Reporting Tools
   - Admins generate reports on submission statistics, evaluation progress, and published papers.
   - Supervisors generate reports for their assigned students.
  
6. Central Research Repository
   - All Accepted & Published papers are stored in a searchable database.
   - Search filters include: Student ID, Program, Category, Date, Author, and Keywords.
  

### Roles and Capabilities
1. **Student** → Submit papers, track status, view feedback, download commented files.
2. **Faculty/Supervisor** → Create assignments, evaluate submissions, upload feedback, publish accepted papers.
3. **Admin** → Manage users, monitor submissions, generate reports, oversee system activity.
### Landing Page  
<img width="1903" height="871" alt="image" src="https://github.com/user-attachments/assets/983c5b10-93c8-46d2-bbe5-f8c2b9c8e952" />  

### Signup Page  
<img width="1842" height="825" alt="image" src="https://github.com/user-attachments/assets/c764c117-0b35-482e-a325-107840b19be4" /> 

### Student Interface  
<img width="1892" height="888" alt="image" src="https://github.com/user-attachments/assets/b13ddb51-dace-4bb1-8b47-0ed11638e679" />  

### Supervisor Interface  
<img width="1887" height="891" alt="image" src="https://github.com/user-attachments/assets/b28bb7d2-19f3-4e32-ab9f-6b8c4be263ce" />

### Admin Interface  
<img width="1890" height="881" alt="image" src="https://github.com/user-attachments/assets/09edd973-05f7-4a42-aaaa-fe5cecc93fe7" />

### User profile  
<img width="1897" height="888" alt="image" src="https://github.com/user-attachments/assets/070cc2ca-308d-4056-b355-d887d7f8d8c8" />

___
### Tools Used
PHP, HTML, CSS, JavaScript, JQuery, MySQL.
___

### Security Practices Applied 
In developing my project, I incorporated several practices inspired by the OWASP Top 10 (2025) to strengthen the overall security of the system.

- To address **Broken Access Control (A01)**, almost all pages in the application perform server‑side role verification, ensuring that only authorized users can access protected resources, with unauthorized users redirected to the login page.
- To reduce the risk of **Injection (A05)**, the system uses secure MySQL prepared statements along with htmlspecialchars() when outputting data to prevent cross‑site scripting.
- For **Authentication Failures (A07)**, user passwords are stored using secure hashing rather than plain text, improving credential protection.
- The system also includes structured logging and admin notifications for technical issues, aligning with **Security Logging and Alerting Failures (A09)** by ensuring that errors are captured.
- I implemented proper exception handling, preventing unexpected failures from exposing sensitive information or disrupting system behavior, which supports the goals of **Mishandling of Exceptional Conditions (A10)**.

### Future Improvements
Although I ensured the project meets its core requirements, I don't plan to stop just there. To continue enhancing the security, usability, and maintainability of the system, here is a list of future enhancements I would like to implement:  

▸ Add Multi‑Factor Authentication (MFA) to strengthen user identity verification.  
▸ Maintain version documentation for all tools, libraries, and Composer packages to ensure the system stays updated and secure.  
▸ Implement login attempt logging to help detect brute‑force attacks or suspicious authentication patterns.  
▸ Introduce a real‑time chat feature between supervisors and students to improve communication within the system.
