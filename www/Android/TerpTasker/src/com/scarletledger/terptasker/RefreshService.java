package com.scarletledger.terptasker;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Random;
import java.util.concurrent.CopyOnWriteArrayList;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.cookie.Cookie;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.client.DefaultHttpRequestRetryHandler;
import org.apache.http.impl.cookie.BasicClientCookie;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.params.HttpParams;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import com.actionbarsherlock.app.SherlockFragmentActivity;
import com.androidquery.AQuery;
import com.androidquery.callback.AjaxCallback;
import com.androidquery.callback.AjaxStatus;
import com.google.gson.Gson;
import com.google.gson.JsonObject;
import com.google.gson.reflect.TypeToken;
import com.scarletledger.terptasker.TTObject.TTCategory;
import com.scarletledger.terptasker.TTObject.TTContext;
import com.scarletledger.terptasker.TTObject.TTConversation;
import com.scarletledger.terptasker.TTObject.TTConversationToSend;
import com.scarletledger.terptasker.TTObject.TaskStruct;

import android.app.AlarmManager;
import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.database.Cursor;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Vibrator;
import android.preference.PreferenceManager;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.provider.ContactsContract.Contacts;
import android.provider.ContactsContract.PhoneLookup;
import android.support.v4.app.FragmentActivity;
import android.support.v4.app.NotificationCompat;
import android.support.v4.app.NotificationCompat.Builder;
import android.util.Log;
import android.util.SparseArray;
import android.widget.Toast;

public class RefreshService extends IntentService{
	public static final int TerpTaskerNotificationID = 87;
	public static final String TASKS_NOTIFIED = "TASKS_NOTIFIED";
	public static final String TASKS_CLEARED = "TASKS_CLEARED";
	public static final String SHARED_PREFS = "MainProgramActivity";
	public static final String FLAG_CLEAR_NOTIFICATIONS = "CLEAR_NOTIFICATIONS";
	public static final String FLAG_CLEAR_AND_LAUNCH_APP = "LAUNCH_APPLICATION";
	public static final String NO_SERVICE_LAUNCH = "NO_SERVICE_LAUNCH";
	public static final String CURRENT_USER_NAME = "CURRENT_USER_NAME";
	public static final String CURRENT_USER_ID = "CURRENT_USER_ID";
	public static final String TASKS_ON_DEVICE = "TASKS";
	public static final String CATEGORIES_ON_DEVICE = "CATEGORIES";
	public static final String CONTEXTS_ON_DEVICE = "CONTEXTS";
	public static final String CONVERSATIONS_ON_DEVICE = "CONVERSATIONS";
	public static final String TIME_OF_LAST_SYNC  = "LAST_SYNC_TIME";

	public RefreshService() {
		super("TerpTasker-Refresh-Service");
	}

	@Override
	protected void onHandleIntent(Intent intent)
	{
		List<TaskStruct> globalTasks;
		List<Integer> notifiedIDs = new ArrayList<Integer>(), clearedIDs = new ArrayList<Integer>();
		Gson gson = new Gson();

		//When a notification is clicked - clear notifications and launch app
		Intent clickNotificationIntent = new Intent(this, RefreshServiceAlarmReceiver.class);
		clickNotificationIntent.setAction(FLAG_CLEAR_AND_LAUNCH_APP);
		final PendingIntent clickNotificationPIntent = PendingIntent.getBroadcast(this, 1, clickNotificationIntent, 0);

		//Get preferences
		final SharedPreferences appSettings = PreferenceManager.getDefaultSharedPreferences(this.getApplicationContext());
		final SharedPreferences appUserData = this.getApplicationContext().getSharedPreferences(SHARED_PREFS, 0);
		final String userName = RefreshService.getCurrentUserName(this);

		boolean showNotifications = appSettings.getBoolean("ShowNotifications", true);
		boolean doTaskSync = appSettings.getBoolean("SyncTasksSetting", true);

		if(!showNotifications && !doTaskSync)
			return;

		if(doTaskSync)
		{
			long syncMin = appSettings.getInt("SyncPeriodMinutes", 15);
			long lastSync = appUserData.getLong(userName + RefreshService.TIME_OF_LAST_SYNC, System.currentTimeMillis());

			if(System.currentTimeMillis() - lastSync - (syncMin * 1000 * 60) >= 0)
				RefreshService.syncFromService(this);
		}

		String tasksJson = appUserData.getString(userName + RefreshService.TASKS_ON_DEVICE, "");

		if(userName == null || tasksJson.equals(""))
			return;

		globalTasks = gson.fromJson(tasksJson, new TypeToken<List<TaskStruct>>() {}.getType());

		if(!appUserData.getString(userName + TASKS_NOTIFIED, "").equals(""))
			notifiedIDs = gson.fromJson(appUserData.getString(userName + TASKS_NOTIFIED, ""), new TypeToken<List<Integer>>() {}.getType());

		if(!appUserData.getString(userName + TASKS_CLEARED, "").equals(""))
			clearedIDs = gson.fromJson(appUserData.getString(userName + TASKS_CLEARED, ""), new TypeToken<List<Integer>>() {}.getType());

		Boolean vibrate = appSettings.getBoolean("VibrateNotifications", true);
		Calendar now = Calendar.getInstance();
		List<TaskStruct> notifiableTasks = new ArrayList<TaskStruct>();
		List<Date> notifyDates = new ArrayList<Date>();

		//Is task within notifiable period? If not, store time to so we know when to refire service
		for(TaskStruct t: globalTasks)
		{
			if(t.reminderMinBefore == -1)
				continue;
			
			Calendar timeToCheck = t.objectType == TTObject.OBJECT_TYPE_TASK ? t.dateDue : t.dateStart;
			if((!clearedIDs.contains(t.taskID) && timeToCheck != null && timeToCheck.compareTo(now) >= 0) || notifiedIDs.contains(t.taskID))
			{
				Calendar temp = Calendar.getInstance();
				temp.setTime(timeToCheck.getTime());
				temp.add(Calendar.MINUTE, -1 * t.reminderMinBefore);

				if(temp.compareTo(now) <= 0 || notifiedIDs.contains(t.taskID))
					notifiableTasks.add(t);
				else
					notifyDates.add(temp.getTime());
			}
		}

		Collections.sort(notifiableTasks, new Comparator<TaskStruct>(){
			@Override
			public int compare(TaskStruct lhs, TaskStruct rhs) {
				Calendar lhsDate = lhs.objectType == TTObject.OBJECT_TYPE_TASK ? lhs.dateDue : lhs.dateStart;
				Calendar rhsDate = rhs.objectType == TTObject.OBJECT_TYPE_TASK ? rhs.dateDue : rhs.dateStart;

				return lhsDate.compareTo(rhsDate);
			}

		});

		//Only renotify if there are new tasks
		if(notifiableTasks.size() > 0 && notifiableTasks.size() != notifiedIDs.size())
		{
			String contentTitle, contentText;
			if(notifiableTasks.size() > 1)
			{
				contentTitle = "Reminder: " + notifiableTasks.size() + " notifications";
				Date time = notifiableTasks.get(notifiableTasks.size() - 1).objectType == TTObject.OBJECT_TYPE_TASK ? 
						notifiableTasks.get(notifiableTasks.size() - 1).dateDue.getTime() : notifiableTasks.get(notifiableTasks.size() - 1).dateStart.getTime();
						contentText = "Last notification for event at: " + MainTaskListFragment.sdfOnlyTime.format(time);
			}
			else
			{
				contentTitle = notifiableTasks.get(0).name + (notifiableTasks.get(0).objectType == TTObject.OBJECT_TYPE_TASK ? " ended" : " starting");
				Date time = notifiableTasks.get(0).objectType == TTObject.OBJECT_TYPE_TASK ? 
						notifiableTasks.get(0).dateDue.getTime() : notifiableTasks.get(0).dateStart.getTime();
						contentText = "Last notification: " + MainTaskListFragment.sdfOnlyTime.format(time);
						contentText = "At time: " + MainTaskListFragment.sdfOnlyTime.format(time);
			}

			for(TaskStruct t : notifiableTasks)
			{
				if(!notifiedIDs.contains(t.taskID))
					notifiedIDs.add(t.taskID);
			}

			Intent i1 = new Intent(this, RefreshServiceAlarmReceiver.class);
			i1.setAction(FLAG_CLEAR_NOTIFICATIONS);
			final PendingIntent pi = PendingIntent.getBroadcast(this, 1, i1, 0);
			Builder nBuilder = new NotificationCompat.Builder(this);
			nBuilder = nBuilder.setSmallIcon(R.drawable.ic_launcher)
					.setContentTitle(contentTitle)
					.setContentText(contentText)
					.setContentIntent(clickNotificationPIntent)
					.setDeleteIntent(pi);

			if(notifiableTasks.size() > 1)
			{
				NotificationCompat.InboxStyle inboxStyle =
						new NotificationCompat.InboxStyle();

				int numEvents = notifiableTasks.size() > 6 ? 6 : notifiableTasks.size();

				inboxStyle.setBigContentTitle("Reminder for last " + notifiableTasks.size() + " tasks:");

				for (int i= 0; i < numEvents; i++) {
					TaskStruct currTsk = notifiableTasks.get(notifiableTasks.size() - 1 - i);

					String tName = currTsk.name.length() > 20 ? currTsk.name.substring(0, 20) + "..." : currTsk.name;
					String prefix = notifiableTasks.get(0).objectType == TTObject.OBJECT_TYPE_TASK ? "End: " : "Start: ";
					Date time;
					try
					{
					time = notifiableTasks.get(0).objectType == TTObject.OBJECT_TYPE_TASK ? currTsk.dateDue.getTime() : currTsk.dateStart.getTime();
					}
					catch(Exception e){
						time = Calendar.getInstance().getTime();
					}
					inboxStyle.addLine(prefix + tName + " @ " + MainTaskListFragment.sdfOnlyTime.format(time));
				}
				nBuilder.setStyle(inboxStyle).setDeleteIntent(pi);
			}

			Notification not = nBuilder.build();
			not.flags |= Notification.FLAG_AUTO_CANCEL;

			if(vibrate)
			{
				Vibrator v = (Vibrator) getSystemService(Context.VIBRATOR_SERVICE);
				v.vibrate(400);
			}

			NotificationManager mNotificationManager = 
					(NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
			// mId allows you to update the notification later on.
			mNotificationManager.notify(TerpTaskerNotificationID, not);
		}

		//Update notified IDs
		Editor e = appUserData.edit();
		e.putString(userName + TASKS_NOTIFIED, gson.toJson(notifiedIDs, new TypeToken<List<Integer>>() {}.getType()));
		e.commit();

		Collections.sort(notifyDates);
		//Fire next event earlier if there is an event
		if(notifyDates.size() != 0 || doTaskSync)
		{
			//ms until next unreported event
			long nextEventMs = notifyDates.size() == 0 ? Long.MAX_VALUE : notifyDates.get(0).getTime() - System.currentTimeMillis();
			long nextRefreshMs = doTaskSync ? appSettings.getLong(userName + RefreshService.TIME_OF_LAST_SYNC, System.currentTimeMillis()) + 
					(appSettings.getInt("SyncPeriodMinutes", 15) * 1000 * 60) - System.currentTimeMillis() : Long.MAX_VALUE;
			
			nextEventMs = nextEventMs > nextRefreshMs ? nextRefreshMs : nextEventMs;
			
			Intent iNext = new Intent(this, RefreshServiceAlarmReceiver.class);
			final PendingIntent piNext = PendingIntent.getBroadcast(this, 1, iNext, 0);

			AlarmManager alarm = (AlarmManager) getSystemService(Context.ALARM_SERVICE);
			alarm.cancel(piNext);
			alarm.set(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + nextEventMs, piNext);
			Log.d("refreshservice", "next event will occur in " + nextEventMs + " ms.");
		}
		else
			Log.d("refreshservice", "refresh service will not report");
	}

	public static void syncFromService(final Context context)
	{
		final AQuery aq = new AQuery(context);
		String url = "http://vulgarity.cs.umd.edu/session_vars.php";

		final SharedPreferences prefs = context.getApplicationContext().
				getSharedPreferences(RefreshService.SHARED_PREFS, 0);
		
		final String uName = RefreshService.getCurrentUserName(context);
		final String hashP = prefs.getString(SplashActivity.savedPasswordField, "");
		
		AjaxCallback<JSONObject> cb = new AjaxCallback<JSONObject>(){

			@Override
			public void callback(String url, JSONObject json, AjaxStatus status) {
				boolean retried = false;
				
				if(json != null)
					doJsonParseTasks(null, aq, prefs, uName, json);
				else if(!retried && SplashPageFragment.postLoginData(context, uName, hashP))
				{
					Log.i("login", "had to get new phpsessid");
					this.cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));
					retried = true;
					aq.ajax(this);				
				}
			}
		};
		cb.url(url).type(JSONObject.class).cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));
		aq.ajax(cb);

	}

	public static void syncFromActivity(final FragmentActivity context, final ProgressDialog progress, final int sideMenuPos, final int tabPos, final boolean restartActivity, final boolean loginActivity)
	{
		final AQuery aq = new AQuery(context);
		String url = "http://vulgarity.cs.umd.edu/session_vars.php";

		final SharedPreferences prefs = context.getApplicationContext().
				getSharedPreferences(RefreshService.SHARED_PREFS, 0);

		final String uName = RefreshService.getCurrentUserName(context);
		final String hashP = prefs.getString(SplashActivity.savedPasswordField, "");
		
		AjaxCallback<JSONObject> cb = new AjaxCallback<JSONObject>(){
			boolean retried = false;
			
			@Override
			public void callback(String url, JSONObject json, AjaxStatus status) {

				if(json != null && doJsonParseTasks(progress, aq, prefs, uName, json))
				{
					if(restartActivity)
					{
						Intent launchIntent = new Intent(context, MainProgramActivity.class);
						launchIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
	
						launchIntent.putExtra(MainTaskListFragment.GO_TO_SIDEMENU, sideMenuPos);
						launchIntent.putExtra(MainTaskListFragment.GO_TO_TAB, tabPos);
	
						context.startActivity(launchIntent);
						context.finish();
					}
					else if(loginActivity)
					{
						Toast.makeText(context, "Login Successful from RefreshService.", Toast.LENGTH_SHORT).show();
						Intent launchProgramIntent = new Intent(context, MainProgramActivity.class);
						context.startActivity(launchProgramIntent);
						context.finish();
					}
				}
				else{
					if(!retried && SplashPageFragment.postLoginData(context, uName, hashP))
					{
						Log.i("login", "had to get new phpsessid");
						this.cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));
						retried = true;
						aq.progress(progress).ajax(this);
					}
					else
					{
						if(loginActivity)
							progress.dismiss();
	
						Toast.makeText(context, "Sync failed. Check your network connection.", Toast.LENGTH_LONG).show();
						RefreshService.clearCurrentUserData(aq.getContext());

					}
				}
			}
		};
		cb.url(url).type(JSONObject.class).cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));

		aq.progress(progress).ajax(cb);

	}

	private static boolean doJsonParseTasks(final ProgressDialog progress, final AQuery aq,
			final SharedPreferences prefs, final String userName, JSONObject json)
		{
			List<TTCategory> parseCats = new ArrayList<TTCategory>();
			List<TTContext> parseConts = new ArrayList<TTContext>();
			List<TaskStruct> parseTasks = new ArrayList<TaskStruct>();
			List<TTConversation> parseConversations = new ArrayList<TTConversation>();
			

			try {
				if(!json.isNull("categories"))
				{
					JSONObject catArray = json.getJSONObject("categories");
					JSONArray catIDs = catArray.names();
					for(int i = 0; i < catArray.length(); i++)
					{
						JSONObject cat = catArray.getJSONObject(catIDs.getString(i));
						parseCats.add(new TTCategory(Integer.parseInt(catIDs.getString(i)), 
								cat.getString("name"), "", cat.getString("color")));
					}
				}

				if(!json.isNull("contexts"))
				{
					JSONObject conArray = json.getJSONObject("contexts");
					JSONArray conIDs = conArray.names();
					for(int i = 0; i < conArray.length(); i++)
					{
						JSONObject con = conArray.getJSONObject(conIDs.getString(i));
						parseConts.add(new TTContext(Integer.parseInt(conIDs.getString(i)), 
								con.getString("name"), "", con.getString("color")));
					}
				}
				
				if(!json.isNull("events"))
				{
					JSONObject eventArray = json.getJSONObject("events");
					JSONArray eventIDs = eventArray.names();
					for(int i = 0; i < eventArray.length(); i++)
					{
						JSONObject event = eventArray.getJSONObject(eventIDs.getString(i));
						Calendar start = Calendar.getInstance(), end = Calendar.getInstance();
						start.setTimeInMillis(event.getLong("start_time") * 1000);
						end.setTimeInMillis(event.getLong("end_time") * 1000);
						String location = event.isNull("location") ? "N/A" : event.getString("location");
						String description = event.isNull("Description") ? "None" : event.getString("Description");
						
						int repeatMode = 0;
						int repeatStatus = !event.isNull("repeatB") && event.getBoolean("repeatB") ? TTObject.IS_REPEATING : TTObject.NOT_REPEATING;
						int repeatFreq = 0;
						Calendar repeatEnd = null;
						
						if(repeatStatus == TTObject.IS_REPEATING)
						{
							repeatEnd = Calendar.getInstance();
							if(event.isNull("repeatEnd") || event.isNull("repeatFreq") || event.isNull("repeatLength"))
								repeatStatus = TTObject.NOT_REPEATING;
							else
							{
								String a = event.getString("repeatLength").toLowerCase();
								if(a.equals("day")) repeatMode = TTObject.REPEAT_MODE_DAY;
								if(a.equals("week")) repeatMode = TTObject.REPEAT_MODE_WEEK;
								if(a.equals("month")) repeatMode = TTObject.REPEAT_MODE_MONTH;
								if(a.equals("year")) repeatMode = TTObject.REPEAT_MODE_YEAR;
								
								repeatEnd.setTimeInMillis(event.getLong("repeatEnd") * 1000);
								repeatFreq = event.getInt("repeatFreq");
							}
						}
						
						int popup_reminder = event.isNull("popup_reminder") || event.getInt("popup_reminder") == -1 ? -1 : event.getInt("popup_reminder") / 60;
						String title = event.isNull("title") ? "No Title" : event.getString("title");
						String url = event.isNull("url") ? null : event.getString("url");
						Boolean allDay = event.isNull("allDay") ? false : event.getBoolean("allDay");
						
						parseTasks.add(new TaskStruct(TTObject.OBJECT_TYPE_EVENT, Integer.parseInt(eventIDs.getString(i)), new ArrayList<Integer>(), title, start, end,
								url, location, -1, event.getInt("categoryID"), description, popup_reminder, 
								repeatStatus, repeatMode, repeatFreq, repeatEnd, allDay, false));
						
					}
				}
				
				if(!json.isNull("time_blocks"))
				{
					JSONObject blocksArray = json.getJSONObject("time_blocks");
					JSONArray blockIDs = blocksArray.names();
					for(int i = 0; i < blocksArray.length(); i++)
					{
						JSONObject block = blocksArray.getJSONObject(blockIDs.getString(i));
						Calendar start = Calendar.getInstance(), end = Calendar.getInstance();
						start.setTimeInMillis(block.getLong("startTime") * 1000);
						end.setTimeInMillis(block.getLong("endTime") * 1000);
						
						int repeatMode = 0;
						Calendar repeatEnd = null;
						int repeatStatus = !block.isNull("repeatB") && block.getBoolean("repeatB") ? TTObject.IS_REPEATING : TTObject.NOT_REPEATING;
						int repeatFreq = 0;
						
						if(repeatStatus == TTObject.IS_REPEATING)
						{
							if(block.isNull("repeatEnd") || block.isNull("repeatFreq") || block.getLong("repeatEnd") == 0 || block.isNull("repeatLength"))
								repeatStatus = TTObject.NOT_REPEATING;
							else
							{
								String a = block.getString("repeatLength").toLowerCase();
								if(a.equals("day")) repeatMode = TTObject.REPEAT_MODE_DAY;
								if(a.equals("week")) repeatMode = TTObject.REPEAT_MODE_WEEK;
								if(a.equals("month")) repeatMode = TTObject.REPEAT_MODE_MONTH;
								if(a.equals("year")) repeatMode = TTObject.REPEAT_MODE_YEAR;
								
								repeatEnd = Calendar.getInstance();
								repeatEnd.setTimeInMillis(block.getLong("repeatEnd") * 1000);
								repeatFreq = block.getInt("repeatFreq");
							}
						}
						
						String cName = "N/A";
						int contextID = block.getInt("contextID");
						for(TTContext c : parseConts)
						{
							if(c.contextID == contextID)
							{
								cName = c.name;
								break;
							}
						}
						
						int popup_reminder = block.isNull("popup_reminder") || block.getInt("popup_reminder") == -1 ? -1 : block.getInt("popup_reminder") / 60;

						parseTasks.add(new TaskStruct(TTObject.OBJECT_TYPE_TIMEBLOCK, Integer.parseInt(blockIDs.getString(i)), new ArrayList<Integer>(), cName + " context block", start, end, null, null, 
								contextID, -1, null, 
								popup_reminder, repeatStatus, repeatMode, repeatFreq, repeatEnd, false, false));
					}
				}
				
				if(!json.isNull("text_messages"))
				{
					JSONObject smsArray = json.getJSONObject("text_messages");
					JSONArray smsIDs = smsArray.names();
					for(int i = 0; i < smsArray.length(); i++)
					{
						JSONObject sms = smsArray.getJSONObject(smsIDs.getString(i));
						
						if(sms.isNull("categoryID"))
							continue;
						
						String textTime = sms.isNull("time") ? null : sms.getString("time");
						String name = sms.isNull("name") ? "No name provided" : sms.getString("name");
						String content = sms.isNull("content") ? "N/A" : sms.getString("content");
						String phonenum = sms.isNull("phonenum") ? "N/A" : sms.getString("phonenum");
						
						parseConversations.add(new TTConversation(TTConversation.TYPE_TEXT_MESSAGE, textTime, sms.getInt("categoryID"), phonenum, 
								name, Integer.parseInt(smsIDs.getString(i)), "", content, null));
					}
				}
				
				if(!json.isNull("call_history"))
				{
					JSONObject callArray = json.getJSONObject("call_history");
					
					JSONArray callIDs = callArray.names();
					for(int i = 0; i < callArray.length(); i++)
					{
						JSONObject call = callArray.getJSONObject(callIDs.getString(i));
						
						if(call.isNull("categoryID"))
							continue;
						
						String textTime = call.isNull("time") ? "N/A" : call.getString("time");
						String name = call.isNull("name") ? "No name provided" : call.getString("name");
						String phonenum = call.isNull("phonenum") ? "N/A" : call.getString("phonenum");
						String duration = call.isNull("duration") ? "N/A" : call.getString("duration");

						
						parseConversations.add(new TTConversation(TTConversation.TYPE_CALL_HISTORY, textTime, call.getInt("categoryID"), phonenum, 
								name, Integer.parseInt(callIDs.getString(i)), duration, null, null));
					}
				}
				
				if(!json.isNull("contacts"))
				{
					JSONObject contactArray = json.getJSONObject("contacts");
					JSONArray contactIDs = contactArray.names();
					for(int i = 0; i < contactArray.length(); i++)
					{
						JSONObject contact = contactArray.getJSONObject(contactIDs.getString(i));
						
						if(contact.isNull("categoryID"))
							continue;
						
						String phone = contact.isNull("phonenum") ? "No phone number" : contact.getString("phonenum");
						String email = contact.isNull("email") ? "No email address" : contact.getString("email");
						String name = contact.isNull("name") ? "N/A" : contact.getString("name");


						parseConversations.add(new TTConversation(TTConversation.TYPE_CONTACT, null, contact.getInt("categoryID"), phone, name, 
								Integer.parseInt(contactIDs.getString(i)), "", null, email));
					}
				}
				
				if(!json.isNull("tasks"))
				{
					JSONObject taskArray = json.getJSONObject("tasks");
					JSONArray taskIDs = taskArray.names();
					for(int i = 0; i < taskArray.length(); i++)
					{
						JSONObject task = taskArray.getJSONObject(taskIDs.getString(i));
						Calendar end = Calendar.getInstance();
	
						if(!task.isNull("duedate"))
						{
							end.setTimeInMillis(task.getLong("duedate") * 1000);
							end.add(Calendar.HOUR, 5);
						}
						else
							end = null;
	
						int popup_reminder = task.isNull("popup_reminder") || task.getInt("popup_reminder") == -1 ? -1 : task.getInt("popup_reminder") / 60;
						boolean completed = task.isNull("completed") || task.getInt("completed") == 0 ? false : true;
						String name = task.isNull("name") ? "N/A" : task.getString("name");
						String dur = task.isNull("estHours") || task.isNull("estMins") ? "N/A" : task.getString("estHours") + " hours, " + task.getString("estMins") + " minutes";
						String desc = task.isNull("description") ? "No description." : task.getString("description");
						
						//Repeating tasks not yet supported db side
						parseTasks.add(new TaskStruct(TTObject.OBJECT_TYPE_TASK, Integer.parseInt(taskIDs.getString(i)), 
								new ArrayList<Integer>(), name, null,
								end, dur, "None", task.getInt("contextID"), task.getInt("categoryID"), desc, popup_reminder, 
										TTObject.NOT_REPEATING, 0, 0, null, false, completed));
	
					}
				}
				
				if(parseTasks.size() > 0)
				{
					int startID = Collections.max(parseTasks, new Comparator<TaskStruct>(){
						@Override
						public int compare(TaskStruct lhs, TaskStruct rhs) {
							Integer a = lhs.taskID;
							Integer b = rhs.taskID;
							return a.compareTo(b);
						}
					}).taskID + 1;
					
					List<TaskStruct> tmpRepeater = new ArrayList<TaskStruct>();
					for(TaskStruct t : parseTasks)
					{
						if(t.dateStart == null || t.dateDue == null)
							continue;
						
						Calendar start = Calendar.getInstance();
						start.setTime(t.dateStart.getTime());
						Calendar end = Calendar.getInstance();
						end.setTime(t.dateDue.getTime());
						if(t.isRepeating == TTObject.IS_REPEATING)
						{
							while(true)
							{
								if(t.repeatMode > TTObject.REPEAT_MODE_YEAR || t.repeatMode < TTObject.REPEAT_MODE_DAY || t.repeatFreq < 1)
									break;
								
								switch(t.repeatMode)
								{
									case TTObject.REPEAT_MODE_DAY: start.add(Calendar.DAY_OF_YEAR, t.repeatFreq); end.add(Calendar.DAY_OF_YEAR, t.repeatFreq); break;
									case TTObject.REPEAT_MODE_WEEK: start.add(Calendar.WEEK_OF_YEAR, t.repeatFreq); end.add(Calendar.WEEK_OF_YEAR, t.repeatFreq); break;
									case TTObject.REPEAT_MODE_MONTH: start.add(Calendar.MONTH, t.repeatFreq); end.add(Calendar.MONTH, t.repeatFreq); break;
									case TTObject.REPEAT_MODE_YEAR:  start.add(Calendar.YEAR, t.repeatFreq); end.add(Calendar.YEAR, t.repeatFreq); break;
								}
	
								Calendar startThis = Calendar.getInstance();
								startThis.setTime(start.getTime());
								Calendar endThis = Calendar.getInstance();
								endThis.setTime(end.getTime());
	
								if(start.compareTo(t.repeatEnd) <= 0)
									tmpRepeater.add(new TaskStruct(t.objectType, startID++, t.relatedTaskIDs, t.name, startThis, endThis, t.timeToCompletion, 
											t.location, t.context, t.category, t.description, t.reminderMinBefore, TTObject.RELATED_REPEATING, t.repeatMode, t.repeatFreq, t.repeatEnd, t.allDay, t.completed));
								else
									break;
							}
						}
					}
					parseTasks.addAll(tmpRepeater);
				}
				
				Gson gson = new Gson();
				Editor edit = prefs.edit();
				edit.putString(userName + RefreshService.TASKS_ON_DEVICE, gson.toJson(parseTasks, new TypeToken<List<TaskStruct>>() {}.getType()));
				edit.putString(userName + RefreshService.CATEGORIES_ON_DEVICE, gson.toJson(parseCats, new TypeToken<List<TTCategory>>() {}.getType()));
				edit.putString(userName + RefreshService.CONTEXTS_ON_DEVICE, gson.toJson(parseConts, new TypeToken<List<TTContext>>() {}.getType()));
				edit.putString(userName + RefreshService.CONVERSATIONS_ON_DEVICE, gson.toJson(parseConversations, new TypeToken<List<TTConversation>>() {}.getType()));
				edit.putLong(userName + RefreshService.TIME_OF_LAST_SYNC, System.currentTimeMillis());
				edit.apply();

				return true;
			} catch (JSONException e) {
				Toast.makeText(aq.getContext(), "JSON parse failure: " + e.getMessage(), Toast.LENGTH_LONG).show();
				if(progress != null)
					progress.dismiss();
			}
			return false;
		}

		public static void syncContactSMSCalls(Context context, ProgressDialog pd)
		{
			List<TTConversationToSend> allData = new ArrayList<TTConversationToSend>();
			HashMap<Integer, TTConversationToSend> contacts = new HashMap<Integer, TTConversationToSend>();

			ContentResolver cr = context.getContentResolver();

			Cursor allContactIDs = cr.query(ContactsContract.Contacts.CONTENT_URI, null, null, null, null);
			Cursor allEmails = cr.query(ContactsContract.CommonDataKinds.Email.CONTENT_URI, null, null, null, null);
			Cursor allPhoneNum = cr.query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI, null, null, null, null);

			pd.incrementProgressBy(15);

			if (allContactIDs.getCount() > 0) 
			{
				while (allContactIDs.moveToNext()) 
				{
					TTConversationToSend contact = new TTConversationToSend();
					contact.type = TTConversation.TYPE_CONTACT;
					contact.contactName = allContactIDs.getString(allContactIDs.getColumnIndex(ContactsContract.Contacts.DISPLAY_NAME));
					contacts.put(Integer.parseInt(allContactIDs.getString(allContactIDs.getColumnIndex(ContactsContract.Contacts._ID))), contact);
				}
				allContactIDs.close();
			}

			pd.incrementProgressBy(15);

			if(allEmails.getCount() > 0)
			{
				while(allEmails.moveToNext())
				{
					TTConversationToSend contact = 
							contacts.get(Integer.parseInt(allEmails.getString(allEmails.getColumnIndex(ContactsContract.CommonDataKinds.Email.CONTACT_ID))));
					contact.email = allEmails.getString(allEmails.getColumnIndex(ContactsContract.CommonDataKinds.Email.ADDRESS));
				}
				allEmails.close();
			}

			pd.incrementProgressBy(20);

			if(allPhoneNum.getCount() > 0)
			{
				while(allPhoneNum.moveToNext())
				{
					TTConversationToSend contact = 
							contacts.get(Integer.parseInt(allPhoneNum.getString(allPhoneNum.getColumnIndex(ContactsContract.CommonDataKinds.Phone.CONTACT_ID))));
					contact.phoneNum = allPhoneNum.getString(allPhoneNum.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER));
				}
				allEmails.close();
			}

			pd.incrementProgressBy(20);

			allData.addAll(contacts.values());

			//SMS
			Cursor contentCursor = context.getContentResolver().query(Uri.parse("content://sms/"), null, null, null, null);

			if(contentCursor.getCount() > 0)
			{
				while(contentCursor.moveToNext())
				{
					String person = "N/A";
					String addr = contentCursor.getString(contentCursor.getColumnIndex("address"));
					String body = contentCursor.getString(contentCursor.getColumnIndex("body"));
	
					long date = -1;
					try
					{
						Long msEpoch = Long.parseLong(contentCursor.getString(contentCursor.getColumnIndex("date")));
						Calendar a = Calendar.getInstance();
						a.setTimeInMillis(msEpoch);
						date = Long.parseLong(MainTaskListFragment.dateTimeFormatter.format(a.getTime()));
					}
					catch(Exception e){}

					Uri uri = Uri.withAppendedPath(PhoneLookup.CONTENT_FILTER_URI, Uri.encode(addr));  
					Cursor cs= context.getContentResolver().query(uri, new String[]{PhoneLookup.DISPLAY_NAME},PhoneLookup.NUMBER+"='"+addr+"'",null,null);

					if(cs.getCount()>0)
					{
						cs.moveToFirst();
						person=cs.getString(cs.getColumnIndex(PhoneLookup.DISPLAY_NAME));
					} 
					cs.close();

					TTConversationToSend conv = new TTConversationToSend();
					conv.type = TTConversation.TYPE_TEXT_MESSAGE;
					conv.contactName = person;
					conv.phoneNum = addr;
					conv.textContent = body;	
					conv.time = date;

					allData.add(conv);
				}
				contentCursor.close();
			}

			pd.incrementProgressBy(15);

			//Calls
			contentCursor =  context.getContentResolver().query(android.provider.CallLog.Calls.CONTENT_URI, 
					null, null, null, null);

			if(contentCursor != null)
			{
				while(contentCursor.moveToNext())
				{
					String num= contentCursor.getString(contentCursor.getColumnIndex(CallLog.Calls.NUMBER));// for  number
					String name= contentCursor.getString(contentCursor.getColumnIndex(CallLog.Calls.CACHED_NAME));// for name
					String duration = contentCursor.getString(contentCursor.getColumnIndex(CallLog.Calls.DURATION));// for duration
					
					long date = -1;
					try
					{
						Long msEpoch = Long.parseLong(contentCursor.getString(contentCursor.getColumnIndex(CallLog.Calls.DATE)));
						Calendar a = Calendar.getInstance();
						a.setTimeInMillis(msEpoch);
						date = Long.parseLong(MainTaskListFragment.dateTimeFormatter.format(a.getTime()));
					}
					catch(Exception e){}
					
					TTConversationToSend call = new TTConversationToSend();
					call.type = TTConversation.TYPE_CALL_HISTORY;
					call.contactName = name;
					call.phoneNum = num;
					call.time = date;

					try
					{
						call.duration = Integer.parseInt(duration);
					} catch(Exception e){}

					allData.add(call);

				}
				contentCursor.close();
			}

			pd.incrementProgressBy(15);
			
			/*
			allData.clear();
			Random r = new Random();
			
			TTConversationToSend a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CALL_HISTORY;
			a.phoneNum = "4109419601";
			a.time = 20131205133400;
			a.contactName = "Eric";
			a.duration = r.nextInt(50);
			a.email = "ericlee@umd.edu";
			a.textContent = "";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CALL_HISTORY;
			a.phoneNum = "18001234567";
			a.time = 20131205115600;
			a.contactName = "The Doctor";
			a.duration = r.nextInt(50);
			a.email = "doctorwho.terptasker@gmail.com";
			a.textContent = "";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CALL_HISTORY;
			a.phoneNum = "2345231232";
			a.time = 20131203091400;
			a.contactName = "Bruce Wayne";
			a.duration = r.nextInt(50);
			a.email = "notbatman@gmail.com";
			a.textContent = "";
			allData.add(a);
			
			//////////////////////
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CONTACT;
			a.phoneNum = "4109419601";
			a.contactName = "Eric";
			a.duration = r.nextInt(50);
			a.email = "ericlee@umd.edu";
			a.textContent = "";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CONTACT;
			a.phoneNum = "18001234567";
			a.contactName = "The Doctor";
			a.duration = r.nextInt(50);
			a.email = "doctorwho.terptasker@gmail.com";
			a.textContent = "";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_CONTACT;
			a.phoneNum = "2345231232";
			a.contactName = "Bruce Wayne";
			a.duration = r.nextInt(50);
			a.email = "notbatman@gmail.com";
			a.textContent = "";
			allData.add(a);
			
			////////////////////////////////////
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_TEXT_MESSAGE;
			a.phoneNum = "4109419601";
			a.time = 20131205053600;
			a.contactName = "Eric";
			a.duration = r.nextInt(50);
			a.email = "ericlee@umd.edu";
			a.textContent = "Don't mess up the presentation";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_TEXT_MESSAGE;
			a.phoneNum = "18001234567";
			a.time = 20131205163500;
			a.contactName = "The Doctor";
			a.duration = r.nextInt(50);
			a.email = "doctorwho.terptasker@gmail.com";
			a.textContent = "i don't know any doctor who references";
			allData.add(a);
			
			a = new TTConversationToSend();
			a.type = TTConversation.TYPE_TEXT_MESSAGE;
			a.phoneNum = "2345231232";
			a.time = 20131205142300;
			a.contactName = "Bruce Wayne";
			a.duration = r.nextInt(50);
			a.email = "notbatman@gmail.com";
			a.textContent = "sup";
			allData.add(a);
			*/
			
			/*
			allData.clear();
			Random r = new Random();
			for(int i = 0; i < 10; i++)
			{
				Calendar c = Calendar.getInstance();
				c.add(Calendar.DAY_OF_YEAR, r.nextInt(50) - 25);
				int mode = r.nextInt(3);
				TTConversationToSend a = new TTConversationToSend();
				a.type = r.nextInt(3);
				a.phoneNum = "4109419601";
				a.contactName = "Eric";
				a.duration = r.nextInt(50);
				a.email = "doctorwho.terptasker@gmail.com";
				a.textContent = "some random text data convo num " + i;
				allData.add(a);
			}
			*/

			Gson gson = new Gson();
			
			String toSend =  gson.toJson(allData, new TypeToken<List<TTConversationToSend>>() {}.getType());
			System.out.println(toSend);
			
			String url = "http://vulgarity.cs.umd.edu/add_tojson.php";
	        
			final AQuery aq = new AQuery(context);
			
			final SharedPreferences prefs = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
		    
			AjaxCallback<String> cb = new AjaxCallback<String>(){
				boolean retried = false;
				@Override
				public void callback(String url, String json, AjaxStatus status) {
					if(json != null && json.contains("Terp") && retried == false)
		        	{
		        		Log.i("login", "had to get new phpsessid");
						this.cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));
						retried = true;
						aq.ajax(this);
		        	}
				}
			};
			cb.param("conversations", toSend).url(url).type(String.class).cookie("PHPSESSID", prefs.getString("PHPSESSID", ""));
			aq.ajax(cb);
		    

		}


		public static String getCurrentUserName(Context context)
		{
			final SharedPreferences prefs = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
			return prefs.getString(CURRENT_USER_NAME, null);
		}

		public static void clearNotificationData(Context context)
		{
			String userName = RefreshService.getCurrentUserName(context);
			if(userName == null)
				return;

			NotificationManager mNotificationManager = 
					(NotificationManager) context.getSystemService(Context.NOTIFICATION_SERVICE);
			mNotificationManager.cancel(RefreshService.TerpTaskerNotificationID);

			final SharedPreferences appUserData = context.getApplicationContext().getSharedPreferences(SHARED_PREFS, 0);
			appUserData.edit().remove(userName + TASKS_NOTIFIED).remove(userName + TASKS_CLEARED).commit();
		}

		public static void setCurrentUserName(Context context, String userName)
		{
			final SharedPreferences prefs = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
			Editor edit = prefs.edit();
			edit.putString(CURRENT_USER_NAME, userName);
			//edit.putString(CURRENT_USER_ID, userID);
			edit.commit();
		}

		public static void clearCurrentUserData(Context context)
		{
			clearNotificationData(context);
			String userName = getCurrentUserName(context);
			final SharedPreferences prefs = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
			Editor edit = prefs.edit();
			edit.remove(CURRENT_USER_NAME);
			edit.remove(CURRENT_USER_ID);
			edit.remove(userName + RefreshService.TASKS_ON_DEVICE);
			edit.remove(userName + RefreshService.CATEGORIES_ON_DEVICE);
			edit.remove(userName + RefreshService.CONTEXTS_ON_DEVICE);
			edit.remove(userName + RefreshService.CONVERSATIONS_ON_DEVICE);
			
			if(!prefs.getBoolean("PERSIST_USER", false))
			{
				edit.remove(SplashActivity.savedEmailField);
				edit.remove(SplashActivity.savedPasswordField);
			}
			
			edit.commit();
		}

		public static void cancelNextRefresh(Context context)
		{
			Intent iNext = new Intent(context, RefreshServiceAlarmReceiver.class);
			final PendingIntent piNext = PendingIntent.getBroadcast(context, 1, iNext, 0);
			AlarmManager alarm = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
			alarm.cancel(piNext);
		}

	}
