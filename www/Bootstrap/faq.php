<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//add the header info & navbar
generateHeader("Text Messages");

?>

<h1 style='margin-top:-15px; float:left;'><small>Help</small></h1>
<br><br>
<b>1. What is a category? <br></b>
Categories are tags which define a project, class, or type of activity.  An example of a tag would be "CMSC435" or "Linear Algebra."  
Categories are often called "Projects" in other productivity applications. <br>
<b>2. What is a context? <br> </b>
A context is a different type of tag which describes the amount of effort needed to complete the task.  We provide three built-in contexts, "1 Cup of Coffee," "2 Cups of Coffee," or "3 Cups of Coffee," with 1 being the tag for the easiest tasks and 3 being the tag for the hardest. <br>
<b>3. What is the difference between Context Views and Category Views? <br> </b>
Context Views shows you all the blocks tagged with the context you select.  Category View shows you all tasks, emails, contacts, events, and files tagged with this category.  If you want to view everything together, choose Calendar from the sidebar, which functions like a regular calendar. <br>
<b>4. What is a context block? <br> </b>
A context block is a portion of time set aside each week to be filled with a certain context.  For example, if you are most productive Monday afternoons, you can schedule a context block Monday 2-4pm and designate it as a "3 Cup of Coffee" context block.  This means Terp Tasker will recommend relevantly tagged tasks for completion during that period. <br>
<b>5. What is an event? <br></b>
An event is a portion of time where there is a designated activity.  Events can have a designated category and be recurring.  For example, an event can be called "Meet with Dr. Purtilo," scheduled for 12/4/13 from 9-10am, and put in the category "CMSC435".  Another event can be called "Class", from 12:30-2pm Tuesdays, also tagged as "CMSC435", but set to be a recurring event. <br>
<b>6. How do I add/edit/delete contexts? <br></b>
To add a new context, click on "+ new context" in the sidebar under "Context Views." In the pop-up, give the new Context a name (required), and a description and color (if desired). <br>
To edit or delete a context, click on the name of the context you want in the sidebar under "Context Views."  When the calendar reloads, in the upper right hand corner, there are Edit and Delete buttons.  Edit will open a pop-up where you can change the context's name, description, or color.  Delete will open up a dialog asking if you are sure you wish to delete.  If you check "Also delete all _____ time blocks", all time blocks tagged with that context will be deleted.  If you leave it unchecked, time blocks tagged as that context will be changed to the context of "None". <br>
<b>7. How do I add/edit/delete categories? <br></b>
To add a new category, click on "+ new category" in the sidebar under "Category Views." In the pop-up, give the new Category a name (required), and a description and color (if desired). <br>
To edit or delete a category, click on the name of the category you want in the sidebar under "Category Views." When the calendar reloads, in the upper right hand corner, there are Edit and Delete buttons. Edit will open a pop-up where you can change the category's name, description, or color. Delete will open up a dialog asking if you are sure you wish to delete. If you check "Also delete all _____ events", all events tagged with that category will be deleted.  If you check "Also delete all _____ tasks", all tasks tagged with that category will be deleted.  If you leave either or both unchecked, events or tasks tagged as that category will be changed to the category of "None".<br> 
<b>8. How do I add/edit/delete events? <br></b>
To add an event, simply click anywhere on the calendar in either Category view or the regular Calendar view.  Fill in the details if your event, then hit Save. Make sure that at least the Name field is completed in the popup menu.  <br>
To edit an event, click on the event in the calendar.  A menu will pop up where you can edit the details of the event.  Make sure to hit Save when you finish. <nr>
To delete an event, click on the event in the calendar.  When the menu pops up, hit the Delete button in the lower right.  <br> 
<b>9. How do I add/edit/delete time blocks? <br></b>
To add a time block, simply click anywhere on the calendar in either Context view or the regular Calendar view.  Fill in the details if your event, then hit Save. Make sure that at least the Name field is completed in the popup menu.  <br>
To edit a time block, click on the time block in the calendar.  A menu will pop up where you can edit the details of the event.  Make sure to hit Save when you finish. <nr>
To delete a time block, click on the event in the calendar.  When the menu pops up, hit the Delete button in the lower right.  <br> 
<b>10. How do I add/edit/delete tasks? <br></b>
To add a task, either go to the All Tasks in the sidebar, or scroll down on a Category view.  To create a task, type the task name in the text box and hit Enter.  <br>
To edit or delete a task, hover over it your mouse then click the down arrow on the right edge of the row.  If you click Edit, this will open up a file for you to edit.  To delete the task, select the Delete option. <br>
<b>11. How do I import a calendar? <br></b>
Terp Tasker can accept calendar imports in the form of a .ics file.  When you have one, go to the Calendar section on the sidebar, then scroll down.  Select your .ics file and hit Import. <br>
<b>12. How do I add texts and phone calls? <br></b>
Texts and phone calls can be added via the Android application and viewed under the Call History and Text Messages on the sidebar under Tag Data. <br>
<b> 13. How do I add contacts?<br></b>
You can upload contacts via the vCard importer on the Contacts page (make sure your vCard has a name, email, and home phone number) or through the Android application. <br>
<b>14. How do I get the Android application? <br></b>
You can download the Android application from our home screen before you login, www.TerpTasker.com.  It only works on Android devices. <br>
<b>15. What does the Android application do? <br></b>
The Android application allows for time-critical access to information on the go.  It allows for Calendar, Category, and Context views. It allows for information input like Call Logs, Text Messages, Calendar information, and Contacts. <br>  
 

		<?php
generateFooter();

	mysqli_close($con);
?>
