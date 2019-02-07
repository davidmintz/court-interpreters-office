
# How the event listeners and triggers are/were wired.

admin `Module.php` attaches a listener that fetches a human-friendly view
of the entity and sticks it in the session for later reference.

*to do* stop doing this when it is not a POST request

this is used for displaying a "diff" following an Event update, showing the user
what has just been changed (and giving JS a chance to reason about whether to
prompt the user to send email to anybody about it)

we also tried using it with the Request module, on update...
