package com.scarletledger.terptasker;

import java.util.ArrayList;

import com.actionbarsherlock.app.SherlockPreferenceActivity;
import com.actionbarsherlock.view.MenuItem;
import com.scarletledger.terptasker.MainTaskListFragment.TaskListAdapter;

import android.app.NotificationManager;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.LayerDrawable;
import android.os.Bundle;
import android.preference.Preference;
import android.preference.Preference.OnPreferenceChangeListener;
import android.preference.Preference.OnPreferenceClickListener;
import android.preference.PreferenceActivity;
import android.preference.PreferenceManager;

public class PreferencesActivity extends SherlockPreferenceActivity {
	  @Override
	  public void onCreate(Bundle savedInstanceState) {
	      super.onCreate(savedInstanceState);
	      final Context context = this;
	      
	      SplashActivity.setActionBarColor(this.getSupportActionBar());
	      getSupportActionBar().setHomeButtonEnabled(true);
	      getSupportActionBar().setDisplayHomeAsUpEnabled(true);
	      addPreferencesFromResource(R.xml.preferences);	      
	      
	      Preference showNotificationsPref = (Preference) findPreference("ShowNotifications");
	      
	      //Restart the service if duration changes
	      showNotificationsPref.setOnPreferenceChangeListener(new OnPreferenceChangeListener()
	      {
			@Override
			public boolean onPreferenceChange(Preference preference,
					Object newValue) {
				SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(context);
				prefs.edit().putBoolean("TaskReminderTime", (Boolean) newValue).commit();
				
				RefreshService.cancelNextRefresh(context);
				RefreshService.clearNotificationData(context);
				
				if((Boolean)newValue)
				{
					Intent i1 = new Intent(context, RefreshService.class);
					stopService(i1);
					RefreshService.cancelNextRefresh(context);
					startService(i1);
				}
				return true;
			}
	      });
	      
	      Preference timeBetweenSync = (Preference) findPreference("SyncTasksDurationSetting");
	      
	      timeBetweenSync.setOnPreferenceChangeListener(new OnPreferenceChangeListener(){

			@Override
			public boolean onPreferenceChange(Preference preference,
					Object newValue) {
				SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(context);
				int mins = 15;
				
				if(newValue.equals("5 Minutes")) mins = 5;
				if(newValue.equals("15 Minutes")) mins = 15;
				if(newValue.equals("30 Minutes")) mins = 30;
				if(newValue.equals("1 Hour")) mins = 60;
				if(newValue.equals("2 Hours")) mins = 120;
				if(newValue.equals("4 Hours")) mins = 240;
				
				prefs.edit().putInt("SyncPeriodMinutes", mins).commit();
				
				Intent i1 = new Intent(context, RefreshService.class);
				stopService(i1);
				RefreshService.cancelNextRefresh(context);
				startService(i1);	
				
				return true;
			}
	    	  
	      });
	      
	      Preference doSync = (Preference) findPreference("SyncTasksSetting");
	      
	      doSync.setOnPreferenceChangeListener(new OnPreferenceChangeListener(){

			@Override
			public boolean onPreferenceChange(Preference preference,
					Object newValue) {
				if((Boolean)newValue)
				{
					Intent i1 = new Intent(context, RefreshService.class);
					stopService(i1);
					RefreshService.cancelNextRefresh(context);
					startService(i1);	
				}
				return true;
			}
	    	  
	      });


	  }
	  
	  @Override
	    public boolean onOptionsItemSelected(MenuItem menuItem)
	    {       
		  	onBackPressed();
		    return true;
	    }
	  
		@Override
		public void onPause()
		{
			overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
			super.onPause();
		}
	} 
