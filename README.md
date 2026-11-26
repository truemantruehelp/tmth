"# tmth" 

## GP and Skill Development Training

Before releasing the PHP files, apply `sql/gp_training.sql` followed by
`sql/gp_training_staff.sql` to the existing TMTH database. Both scripts can
be applied again and preserve existing requests. Use HTTPS for the public
intake and staff workspaces.

1. The doctor or trainer registers a regular TMTH account.
2. An administrator signs in and opens **GP and training requests → Staff access**.
   Confirm the member of staff owns the account before assigning access.
   Enter the account email and choose **Doctor** or **Trainer**. Each account
   can hold one programme assignment.
3. Staff sign in with their regular credentials and go straight to their
   queue. The public programme page also links to the staff workspace.
4. Workers see only requests belonging to their programme. They can call the
   applicant and mark the request **New**, **Contacted** or **Closed**.
   An administrator can remove access from the Staff access page. Role changes
   are checked again on each workspace request.

The public form collects the applicant's name, mobile number and programme.
TMTH staff arrange appointments and training places by phone. Status changes
are recorded in `tmth_program_status_history`.
