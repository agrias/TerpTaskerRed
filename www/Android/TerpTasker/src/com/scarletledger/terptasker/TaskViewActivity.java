package com.scarletledger.terptasker;

import android.graphics.Paint;
import android.os.Bundle;
import android.support.v4.app.FragmentTransaction;
import android.text.Html;
import android.text.util.Linkify;
import android.widget.TextView;

import com.actionbarsherlock.app.SherlockFragmentActivity;
import com.actionbarsherlock.view.MenuItem;
import com.scarletledger.terptasker.TTObject.TaskStruct;

public class TaskViewActivity extends SherlockFragmentActivity{
	
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.single_task_view);
		
        SplashActivity.setActionBarColor(this.getSupportActionBar());
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        
		TaskStruct task = getIntent().getExtras().getParcelable("Task");
		((TextView)this.findViewById(R.id.titleTask)).setText(task.name);
		
		if(task.objectType == TTObject.OBJECT_TYPE_TASK && task.completed)
			((TextView)this.findViewById(R.id.titleTask)).setPaintFlags(((TextView)this.findViewById(R.id.titleTask)).getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
		
		//Possible null fields
		String location = task.location == null || task.location.length() == 0 ? "None" : task.location;
		String dateStart = task.dateStart == null ? "No start date" : MainTaskListFragment.sdf.format(task.dateStart.getTime());
		String dateDue = task.dateDue == null ? "No due date" : MainTaskListFragment.sdf.format(task.dateDue.getTime());
		
		if(task.dateStart != null)
			((TextView)this.findViewById(R.id.timeTaskStart)).setText(Html.fromHtml("<b>Starts:</b> " + dateStart));
		else
			((TextView)this.findViewById(R.id.timeTaskStart)).setHeight(0);
		
		String taskDuePrefix = task.objectType == TTObject.OBJECT_TYPE_TASK ? "<b>Due:</b> " : "<b>Ends:</b> ";
		((TextView)this.findViewById(R.id.timeTaskDue)).setText(Html.fromHtml(taskDuePrefix + dateDue));
		
		if(task.objectType == TTObject.OBJECT_TYPE_TASK)
			((TextView)this.findViewById(R.id.locationTask)).setHeight(0);
		else
			((TextView)this.findViewById(R.id.locationTask)).setText(Html.fromHtml("<b>Location:</b> " + location));
		
		
		if(task.objectType == TTObject.OBJECT_TYPE_TIMEBLOCK)
			((TextView)this.findViewById(R.id.categoryTask)).setHeight(0);
		else
			((TextView)this.findViewById(R.id.categoryTask)).setText(Html.fromHtml("<b>Category:</b> " + TTObject.getCategoryFromId(task.category).name));
		
		if(task.objectType == TTObject.OBJECT_TYPE_EVENT)
		{
			if(task.timeToCompletion != null && task.timeToCompletion.length() > 0)
			{
				((TextView)this.findViewById(R.id.contextTask)).setText(Html.fromHtml("<b>URL:</b> " + task.timeToCompletion));
				Linkify.addLinks(((TextView)this.findViewById(R.id.contextTask)), Linkify.WEB_URLS);
			}
			else
				((TextView)this.findViewById(R.id.contextTask)).setHeight(0);
		}
		else
			((TextView)this.findViewById(R.id.contextTask)).setText(Html.fromHtml("<b>Context:</b> " + TTObject.getContextFromId(task.context).name));
		
		if(task.objectType == TTObject.OBJECT_TYPE_TASK)
		{
			((TextView)this.findViewById(R.id.taskRepeatStatus)).setHeight(0);
			((TextView)this.findViewById(R.id.estimatedTimeRemainTask)).setText(Html.fromHtml("<b>Time Remaining:</b> " + task.timeToCompletion));
		}
		else if(task.isRepeating == TTObject.IS_REPEATING || task.isRepeating == TTObject.RELATED_REPEATING)
		{
			((TextView)this.findViewById(R.id.taskRepeatStatus)).setText(Html.fromHtml("<b>Repeats until:</b> " + MainTaskListFragment.sdfOnlyDay.format(task.repeatEnd.getTime())));
			
			String repeatInterval = "";
			switch(task.repeatMode)
			{
				case TTObject.REPEAT_MODE_DAY: repeatInterval = "days"; break;
				case TTObject.REPEAT_MODE_WEEK: repeatInterval = "weeks"; break;
				case TTObject.REPEAT_MODE_MONTH: repeatInterval = "months"; break;
				case TTObject.REPEAT_MODE_YEAR: repeatInterval = "years"; break;
			}
			
			((TextView)this.findViewById(R.id.estimatedTimeRemainTask)).setText(Html.fromHtml("<b>Repeats every:</b> " + task.repeatFreq + " " + repeatInterval));
		}
		else
		{
			((TextView)this.findViewById(R.id.taskRepeatStatus)).setHeight(0);
			((TextView)this.findViewById(R.id.estimatedTimeRemainTask)).setHeight(0);
		}
		
		if(task.description != null && task.description.length() > 0)
			((TextView)this.findViewById(R.id.descriptionTask)).setText(Html.fromHtml("<b>Description:</b> " + task.description));
		else
			((TextView)this.findViewById(R.id.descriptionTask)).setHeight(0);
		
		FragmentTransaction ft = this.getSupportFragmentManager().beginTransaction();
		TaskViewFragment taskFrag;
		
		if(task.objectType == TTObject.OBJECT_TYPE_TIMEBLOCK)
			taskFrag = TaskViewFragment.newInstance(TTObject.getContextFromId(task.context).name, MainTaskListFragment.MODE_VIEW_CONTEXT, 0);
		else
			taskFrag = TaskViewFragment.newInstance(task.taskID+"", MainTaskListFragment.MODE_VIEW_RELATED, 0);
		ft.replace(R.id.content_frame_related_tasks, taskFrag);
		ft.commit();
		
		
	}
	
	 @Override 
	 public void onBackPressed() { 
		 super.onBackPressed(); 
		 overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right); 
	 }
	 
	  @Override
	    public boolean onOptionsItemSelected(MenuItem menuItem)
	    {       
		  	onBackPressed();
		    return true;
	    }
}
