package com.scarletledger.terptasker;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;
import java.util.Random;
import java.util.concurrent.CopyOnWriteArrayList;

import com.actionbarsherlock.app.SherlockDialogFragment;
import com.actionbarsherlock.app.SherlockFragmentActivity;
import com.actionbarsherlock.view.MenuItem;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.scarletledger.terptasker.TTObject.TTCategory;
import com.scarletledger.terptasker.TTObject.TTContext;
import com.scarletledger.terptasker.TTObject.TTConversation;
import com.scarletledger.terptasker.TTObject.TaskStruct;

import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;
import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.Dialog;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.content.res.Configuration;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.app.ActionBarDrawerToggle;
import android.support.v4.widget.DrawerLayout;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.Toast;
import android.support.v4.view.GravityCompat;
import android.text.Html;

public class MainProgramActivity extends SherlockFragmentActivity {
	public static List<TaskStruct> globalTasks;
	public static List<TTCategory> globalCategories;
	public static List<TTContext> globalContexts;
	public static List<TTConversation> globalConversations;

	public static DrawerLayout mDrawerLayout;
	public static ListView mDrawerList;

	private ActionBarDrawerToggle mDrawerToggle;
	private MenuListAdapter mMenuAdapter;

	final private String[] mSideMenuTitles = new String[] { "<b>Calendar View</b>", "<b>Category View</b>", "<b>Context View</b>", "<b>Conversations</b>", "<b>Settings</b>", "<b>Logout</b>" };
	final private String[] mSideMenuSubtitles = new String[] { "See all of your tasks", "Catch up on a project", "Task recommendations", "View tagged calls/texts", "Customize Terp Tasker", "Return to login screen" };
	final private int[] mSideMenuIcons = new int[] { R.drawable.calendar_icon, R.drawable.clipboard_icon, R.drawable.context_icon, R.drawable.conversation_icon, R.drawable.action_settings, R.drawable.logout_icon};

	private MainTaskListFragment mCalendarFragment = new MainTaskListFragment();
	private MainTaskListFragment mCategoryFragment = new MainTaskListFragment();
	private MainTaskListFragment mContextFragment = new MainTaskListFragment();
	private MainTaskListFragment mConversationFragment = new MainTaskListFragment();

	private CharSequence mDrawerTitle;
	private CharSequence mTitle;
	private String mUserName;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		Intent intent = this.getIntent();
		if(intent == null || intent.getAction() == null || !intent.getAction().equals(RefreshService.NO_SERVICE_LAUNCH))
		{
			Intent i1 = new Intent(this, RefreshService.class);
			this.stopService(i1);
			RefreshService.cancelNextRefresh(this);
			startService(i1);
		}

		final SharedPreferences prefs = getPreferences(0);
		mUserName = RefreshService.getCurrentUserName(this);

		//TODO: Session var
		if(mUserName == null)
		{
			Intent i1 = new Intent(this, SplashActivity.class);
			this.startActivity(i1);
			this.finish();
		}

		String tasksJson = prefs.getString(mUserName + RefreshService.TASKS_ON_DEVICE, "");
		String categoriesJson = prefs.getString(mUserName + RefreshService.CATEGORIES_ON_DEVICE, "");
		String contextsJson = prefs.getString(mUserName + RefreshService.CONTEXTS_ON_DEVICE, "");
		String conversationsJson = prefs.getString(mUserName + RefreshService.CONVERSATIONS_ON_DEVICE, "");
		Gson gson = new Gson();

		globalTasks = gson.fromJson(tasksJson, new TypeToken<List<TaskStruct>>() {}.getType());
		globalCategories = gson.fromJson(categoriesJson, new TypeToken<List<TTCategory>>() {}.getType());
		globalContexts = gson.fromJson(contextsJson, new TypeToken<List<TTContext>>() {}.getType());
		globalConversations = gson.fromJson(conversationsJson, new TypeToken<List<TTConversation>>() {}.getType());
		
		Collections.sort(globalCategories, new Comparator<TTCategory>(){
			@Override
			public int compare(TTCategory lhs, TTCategory rhs) {
				Integer l = lhs.categoryID, r = rhs.categoryID;
				return l.compareTo(r);
			}
		});
		
		Collections.sort(globalContexts, new Comparator<TTContext>(){
			@Override
			public int compare(TTContext lhs, TTContext rhs) {
				Integer l = lhs.contextID, r = rhs.contextID;
				return l.compareTo(r);
			}
		});
		
		if(globalCategories.size() == 0)
			globalCategories.add(new TTCategory(0, "You have no categories!", "", "FFFFFF"));
		if(globalContexts.size() == 0)
			globalContexts.add(new TTContext(0, "You have no contexts!", "", "FFFFFF"));
		
		String[] catNames = new String[globalCategories.size()], conNames = new String[globalContexts.size()];
		for(int i = 0; i < globalCategories.size(); i++)
			catNames[i] = globalCategories.get(i).name;
		for(int i = 0; i < globalContexts.size(); i++)
			conNames[i] = globalContexts.get(i).name;
		
		mCalendarFragment.setArguments(mCalendarFragment.createArgs
				(new String[] {"Week", "Month"}, true, MainTaskListFragment.MODE_VIEW_CALENDAR));
		mCategoryFragment.setArguments(mCategoryFragment.createArgs(
				catNames, false, MainTaskListFragment.MODE_VIEW_CATEGORY));
		mContextFragment.setArguments(mContextFragment.createArgs(
				conNames, false, MainTaskListFragment.MODE_VIEW_CONTEXT));
		mConversationFragment.setArguments(mContextFragment.createArgs(
				catNames, false, MainTaskListFragment.MODE_VIEW_CONVERSATION));

		//Drawer setup
		setContentView(R.layout.main_application_drawer);

		mTitle = mDrawerTitle = "<b>Terp Tasker</b>";

		mDrawerLayout = (DrawerLayout) findViewById(R.id.drawer_layout);
		mDrawerList = (ListView) findViewById(R.id.listview_drawer);

		mDrawerLayout.setDrawerShadow(R.drawable.drawer_shadow,
				GravityCompat.START);

		mMenuAdapter = new MenuListAdapter(MainProgramActivity.this, mSideMenuTitles, mSideMenuSubtitles,
				mSideMenuIcons);

		mDrawerList.setAdapter(mMenuAdapter);

		mDrawerList.setOnItemClickListener(new DrawerItemClickListener());

		getSupportActionBar().setHomeButtonEnabled(true);
		getSupportActionBar().setDisplayHomeAsUpEnabled(true);

		mDrawerToggle = new ActionBarDrawerToggle(this, mDrawerLayout,
				R.drawable.ic_drawer, R.string.drawer_open,
				R.string.drawer_closed) {

			public void onDrawerClosed(View view) {
				getSupportActionBar().setTitle(Html.fromHtml(mTitle.toString()));
				super.onDrawerClosed(view);
			}

			public void onDrawerOpened(View drawerView) {
				getSupportActionBar().setTitle(Html.fromHtml(mDrawerTitle.toString()));
				super.onDrawerOpened(drawerView);
			}
		};

		mDrawerLayout.setDrawerListener(mDrawerToggle);

		if (savedInstanceState == null) {
			if(intent != null && intent.getExtras() != null)
			{
				//Coming from a refresh? Go back to where we were
				Bundle args = intent.getExtras();
				if(args.containsKey(MainTaskListFragment.GO_TO_SIDEMENU))
				{
					int sel = args.getInt(MainTaskListFragment.GO_TO_SIDEMENU);
					if(args.containsKey(MainTaskListFragment.GO_TO_TAB))
					{
						int col = args.getInt(MainTaskListFragment.GO_TO_TAB);
						Bundle a;
						switch(sel)
						{
						case 0:
							a = mCalendarFragment.getArguments();
							a.putInt(MainTaskListFragment.GO_TO_TAB, col);
							mCalendarFragment.setArguments(a);
							break;
						case 1:
							a = mCategoryFragment.getArguments();
							a.putInt(MainTaskListFragment.GO_TO_TAB, col);
							mCategoryFragment.setArguments(a);
							break;
						case 2:
							a = mContextFragment.getArguments();
							a.putInt(MainTaskListFragment.GO_TO_TAB, col);
							mContextFragment.setArguments(a);
							break;
						case 3:
							a = mConversationFragment.getArguments();
							a.putInt(MainTaskListFragment.GO_TO_TAB, col);
							mConversationFragment.setArguments(a);
							break;
						}
					}
					selectItem(sel);
					return;
				}
			}
			
			int currCategoryEvent = TTObject.isEventCurrentlyOccuring(catNames, globalTasks);
			if(currCategoryEvent != -1)
			{
				Bundle a = mCategoryFragment.getArguments();
				a.putInt(MainTaskListFragment.GO_TO_TAB, currCategoryEvent);
				mCategoryFragment.setArguments(a);
				selectItem(1);
				return;
			}
			
			int currConBlock = TTObject.isTimeBlockCurrentlyOccuring(conNames, globalTasks);
			if(currConBlock != -1)
			{
				Bundle a = mContextFragment.getArguments();
				a.putInt(MainTaskListFragment.GO_TO_TAB, currConBlock);
				mContextFragment.setArguments(a);
				selectItem(2);
				return;
			}
			
			selectItem(0);
			return;
		}
	}

	// When item clicked, close drawer, pause for animation, then commit fragment
	private class DrawerItemClickListener implements
	ListView.OnItemClickListener {
		@Override
		public void onItemClick(AdapterView<?> parent, View view, final int position,
				long id) {
			mDrawerLayout.closeDrawer(mDrawerList);
			new Handler().postDelayed(new Runnable(){

				@Override
				public void run() {
					selectItem(position);
				}
			}, 275);
		}
	}
	
	//Fragment transaction based on actionbar selection
	private void selectItem(int position) { 
		FragmentManager fm = getSupportFragmentManager();
		FragmentTransaction ft = fm.beginTransaction();
		ft.setCustomAnimations(R.anim.slide_in_left, R.anim.slide_out_right);

		switch (position) {
		case 0:
			ft.replace(R.id.content_frame, mCalendarFragment);
			break;
		case 1:
			ft.replace(R.id.content_frame, mCategoryFragment);
			break;
		case 2:
			ft.replace(R.id.content_frame, mContextFragment);
			break;
		case 3:
			ft.replace(R.id.content_frame, mConversationFragment);
			break;
		case 4:
			Intent i = new Intent(getApplicationContext(), PreferencesActivity.class);
			startActivity(i);
			overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
			break;
		case 5:
			//Cancel next notification alarm
			RefreshService.cancelNextRefresh(this);
			RefreshService.clearNotificationData(this);
			RefreshService.clearCurrentUserData(this);

			//TODO - what to remove from phone? session id?
			Intent launchProgramIntent = new Intent(getApplicationContext(), SplashActivity.class);
			startActivity(launchProgramIntent);
			overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
			finish();
			break;
		}
		ft.commit();
		mDrawerList.setItemChecked(position, true);
		setTitle(mSideMenuTitles[position]);
	}

	@Override
	protected void onPostCreate(Bundle savedInstanceState) {
		super.onPostCreate(savedInstanceState);
		mDrawerToggle.syncState();
	}

	@Override
	public void onConfigurationChanged(Configuration newConfig) {
		super.onConfigurationChanged(newConfig);
		mDrawerToggle.onConfigurationChanged(newConfig);
	}

	@Override
	public void setTitle(CharSequence title) {
		if(!title.equals("<b>Settings</b>") && !title.equals("<b>Sync Now</b>") && !title.equals("<b>Logout</b>"))
		{
			mTitle = title;
			getSupportActionBar().setTitle(Html.fromHtml(mTitle.toString()));
		}
	}

	@Override
	public void onBackPressed() {
		return;
	}

}
