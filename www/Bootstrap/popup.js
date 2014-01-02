var interval_sec = 30; //interval in SECONDS to check for reminders

function check_popup(){
   //send AJAX get request
   $.get("check_reminder.php", "interval=" + interval_sec , setup_timers, "json");
}

//setup timers for each reminder
function setup_timers(json){
    events = json.events;
    for(var i = 0; i < events.length; i++){
        setup_event(events[i]);
    } 
    tasks = json.tasks;
    for(var i = 0; i < tasks.length; i++){
	setup_task(tasks[i]);
    }
    blocks = json.blocks;
    for(var i = 0; i < blocks.length; i++){
	setup_block(blocks[i]);
    }
}

months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

function setup_event(event){
    timer_sec = (event.startTime - event.popupReminder) -  Math.round(new Date().getTime() /1000);
    start = new Date(parseInt(event.startTime) * 1000);
    setTimeout(function(){ alert(event.title + " will start at " + start.getHours() + ":" + (start.getMinutes()<10?'0':'') + start.getMinutes() + " on " + months[start.getMonth()] + " " + start.getDate() + ", " + start.getFullYear() )}, timer_sec * 1000);
}

function setup_task(task){
    timer_sec = (task.duedate - task.popupReminder) -  Math.round(new Date().getTime() /1000);
    start = new Date(parseInt(task.duedate) * 1000);
    setTimeout(function(){ alert(task.title + " is due at " + start.getHours() + ":" + (start.getMinutes()<10?'0':'') + start.getMinutes() + " on " + months[start.getMonth()] + " " + start.getDate() + ", " + start.getFullYear() )}, timer_sec * 1000);

}

function setup_block(block){
    timer_sec = (block.startTime - block.popupReminder) -  Math.round(new Date().getTime() /1000);
    start = new Date(parseInt(block.startTime) * 1000);
    setTimeout(function(){ alert(block.name + " will start at " + start.getHours() + ":" + (start.getMinutes()<10?'0':'') + start.getMinutes() + " on " + months[start.getMonth()] + " " + start.getDate() + ", " + start.getFullYear() )}, timer_sec * 1000);

}
