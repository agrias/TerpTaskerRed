package com.scarletledger.terptasker;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.util.Log;

public class RefreshServiceAlarmReceiver extends BroadcastReceiver{

	//Receives broadcast requests - can either spin up the notification service for a run, or launch the app
	@Override
	public void onReceive(Context context, Intent intent) {
		if(intent != null && intent.getAction() != null && 
				(intent.getAction().equals(RefreshService.FLAG_CLEAR_NOTIFICATIONS) || intent.getAction().equals(RefreshService.FLAG_CLEAR_AND_LAUNCH_APP)))
		{
			final SharedPreferences userAppData = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
			final String userName = RefreshService.getCurrentUserName(context);

			if(userName == null)
				return;

			List<Integer> clearedIDs = new ArrayList<Integer>(), notifiedIDs = new ArrayList<Integer>();
			Gson gson = new Gson();
			if(!userAppData.getString(userName + RefreshService.TASKS_CLEARED, "").equals(""))
				clearedIDs = gson.fromJson(userAppData.getString(userName + RefreshService.TASKS_CLEARED, ""), new TypeToken<List<Integer>>() {}.getType());
			if(!userAppData.getString(userName + RefreshService.TASKS_NOTIFIED, "").equals(""))
				notifiedIDs = gson.fromJson(userAppData.getString(userName +  RefreshService.TASKS_NOTIFIED, ""), new TypeToken<List<Integer>>() {}.getType());

			clearedIDs.addAll(notifiedIDs);
			notifiedIDs.clear();

			Editor e = userAppData.edit();
			e.putString(userName + RefreshService.TASKS_NOTIFIED, "");
			e.putString(userName + RefreshService.TASKS_CLEARED, gson.toJson(clearedIDs, new TypeToken<List<Integer>>() {}.getType()));
			e.commit();

			if(intent.getAction().equals(RefreshService.FLAG_CLEAR_AND_LAUNCH_APP))
			{
				Intent launchIntent = new Intent(context, MainProgramActivity.class);
				launchIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
				launchIntent.setAction(RefreshService.NO_SERVICE_LAUNCH);
				context.startActivity(launchIntent);
			}

			return;
		}

		Intent i = new Intent(context, RefreshService.class);
		context.startService(i);
	}
}
