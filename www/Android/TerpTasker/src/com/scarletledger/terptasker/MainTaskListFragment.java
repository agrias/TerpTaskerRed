package com.scarletledger.terptasker;

import java.lang.reflect.Field;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;
import com.actionbarsherlock.app.SherlockFragment;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuInflater;
import com.actionbarsherlock.view.MenuItem;
import com.astuetz.viewpager.extensions.PagerSlidingTabStrip;
import com.scarletledger.terptasker.TTObject.TTCategory;
import com.scarletledger.terptasker.TTObject.TTContext;
import com.scarletledger.terptasker.TTObject.TaskStruct;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.graphics.Paint;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.text.Html;
import android.util.Log;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.LinearLayout;
import android.widget.TextView;


//This more or less encompasses what a generic task view looks like - can pass a mode to change the view
public class MainTaskListFragment extends SherlockFragment {

	//Viewing modes
	public static final int CALENDAR_WEEK_VIEW = 0;
	public static final int CALENDAR_MONTH_VIEW = 1;
	public static final int MODE_VIEW_CONTEXT = 0;
	public static final int MODE_VIEW_CATEGORY = 1;
	public static final int MODE_VIEW_CALENDAR = 2;
	public static final int MODE_VIEW_RELATED = 3;
	public static final int MODE_VIEW_CONVERSATION = 4;
	public static final String GO_TO_SIDEMENU = "JUMP_TO";
	public static final String GO_TO_TAB = "GO_TO_TAB";

	//Sorting modes
	public static final int MODE_SORT_NAME = 0;
	public static final int MODE_SORT_TIME = 1;
	public static final int MODE_SORT_CONTEXT = 2;
	public static final int MODE_SORT_CATEGORY = 3;

	//How dates should be parsed and converted (for minute sensitive and day sensitive times)
	public static SimpleDateFormat sdf = new SimpleDateFormat("MM/dd/yyyy, hh:mm aa");
	public static SimpleDateFormat sdfOnlyDay = new SimpleDateFormat("MM/dd/yyyy");
	public static SimpleDateFormat sdfOnlyTime = new SimpleDateFormat("hh:mm aa");
	public static SimpleDateFormat dateTimeFormatter = new SimpleDateFormat("yyyyMMddhhmmss");

	private View mRootView;
	private ViewPager mTabPager;
	private FragmentPagerAdapter mAdapter = null;
	private String[] mTabNames;
	private Boolean mIsCalView;
	private int mDisplayMode;
	private long timeAtCreation = 0;

	@Override
	public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
		if(mDisplayMode == MODE_VIEW_CONVERSATION)
			inflater.inflate(R.menu.action_bar_menu_conversation, menu);
		else
			inflater.inflate(R.menu.action_bar_menu, menu);	
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {

		if (item.getItemId() == android.R.id.home) {

			if (MainProgramActivity.mDrawerLayout.isDrawerOpen(MainProgramActivity.mDrawerList)) {
				MainProgramActivity.mDrawerLayout.closeDrawer(MainProgramActivity.mDrawerList);
			} else {
				MainProgramActivity.mDrawerLayout.openDrawer(MainProgramActivity.mDrawerList);
			}
		}

		return super.onOptionsItemSelected(item);
	}
	
	@Override
	public void onResume()
	{
		super.onResume();
		SharedPreferences appSettings = this.getSherlockActivity().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
		long lastSync = appSettings.getLong(RefreshService.getCurrentUserName(this.getSherlockActivity()) + RefreshService.TIME_OF_LAST_SYNC, 0);
		if(timeAtCreation != 0 && lastSync > timeAtCreation)
		{
			Intent launchIntent = new Intent(this.getSherlockActivity(), MainProgramActivity.class);
			launchIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
			
			int sideMenuPos = 0;
			switch(mDisplayMode)
			{
			case MODE_VIEW_CONTEXT: sideMenuPos = 2; break;
			case MODE_VIEW_CATEGORY: sideMenuPos = 1; break;
			case MODE_VIEW_CALENDAR: sideMenuPos = 0; break;
			case MODE_VIEW_RELATED: sideMenuPos = 0; break;
			case MODE_VIEW_CONVERSATION: sideMenuPos = 3; break;
			}

			launchIntent.putExtra(MainTaskListFragment.GO_TO_SIDEMENU, sideMenuPos);
			launchIntent.putExtra(MainTaskListFragment.GO_TO_TAB, mTabPager.getCurrentItem());

			this.getSherlockActivity().startActivity(launchIntent);
			this.getSherlockActivity().finish();
		}
		
	}

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
		Bundle arg = getArguments();
		timeAtCreation = System.currentTimeMillis();
		mTabNames = arg.getStringArray("TAB_NAMES");    
		mDisplayMode = arg.getInt("DISPLAY_MODE");
		mIsCalView = arg.getBoolean("IS_CAL_VIEW");

		//Create generic fragment, set up viewpager
		mRootView = inflater.inflate(R.layout.fragment_main_dummy, container, false);
		mTabPager = (ViewPager)mRootView.findViewById(R.id.main_fragment_pager);
		mTabPager.setOffscreenPageLimit(mTabNames.length);

		mAdapter = new TaskPagerAdapter(getChildFragmentManager());
		mTabPager.setAdapter(mAdapter);

		final int pageMargin = (int) TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_DIP, 4, getResources()
				.getDisplayMetrics());
		mTabPager.setPageMargin(pageMargin);
		PagerSlidingTabStrip tabs = (PagerSlidingTabStrip) mRootView.findViewById(R.id.tabStrip);
		tabs.setViewPager(mTabPager);
		tabs.setIndicatorColor(Color.parseColor("#a90000"));
		
		SplashActivity.setActionBarColor(getSherlockActivity().getSupportActionBar());

		setHasOptionsMenu(true);   

		mAdapter.notifyDataSetChanged();
		
		if(arg.getInt(GO_TO_TAB) != 0 && arg.getInt(GO_TO_TAB) < mTabNames.length)
			mTabPager.setCurrentItem(arg.getInt(GO_TO_TAB), true);
		
		return mRootView;
	}

	//When creating instance of task list pass names of tabs (categories, contexts), and viewtype
	public Bundle createArgs(String[] tabNames, Boolean isCalView, int displayMode)
	{
		Bundle bundle = new Bundle();
		bundle.putStringArray("TAB_NAMES", tabNames);
		bundle.putBoolean("IS_CAL_VIEW", isCalView);
		bundle.putInt("DISPLAY_MODE", displayMode);
		return bundle;
	}

	//Calls the correct fragment (calendar/task/conversation)
	public class TaskPagerAdapter extends FragmentPagerAdapter {

		public TaskPagerAdapter(FragmentManager fm) {
			super(fm);
		}

		@Override
		public CharSequence getPageTitle(int position) {
			return mTabNames[position];
		}

		@Override
		public int getCount() {
			return mTabNames.length;
		}

		//If calendar view, display correct fragment
		@Override
		public Fragment getItem(int position) {
			if(mIsCalView)
				return CalendarViewFragment.newInstance(position);
			if(mDisplayMode == MODE_VIEW_CONVERSATION)
				return ConversationViewFragment.newInstance(mTabNames[position], position);
			else
				return TaskViewFragment.newInstance(mTabNames[position], mDisplayMode, position);
		}
	}

	//Class that displays the main info of a task, can be clicked. Is used in almost all app views
	public static class TaskListAdapter extends BaseAdapter{
		private LayoutInflater mInflater;
		private List<TaskStruct> origListTasks;
		private List<TaskStruct> listTasks;
		public int displayMode;
		public String criteria;
		public int prevSortMode = MODE_SORT_NAME;

		public TaskListAdapter(Context context,
				List<TaskStruct> listTasks, int viewMode, String criteria) {
			this.displayMode = viewMode;
			this.criteria = criteria;

			mInflater = LayoutInflater.from(context);
			this.listTasks = new ArrayList<TaskStruct>();
			reEvaluateCriteria(listTasks, Calendar.getInstance(), criteria);
		}
		
		public int currTimeTaskIndex()
		{
			Calendar c = Calendar.getInstance();
			if(listTasks.size() == 0)
				return 0;
			
			for(int pos = 0; pos < listTasks.size() - 1; pos++)
			{
				TaskStruct t = listTasks.get(pos);
				if (t.dateDue != null && c.compareTo(t.dateDue) <= 0)
					return pos;
			}
			
			return listTasks.size() - 1;
		}

		public void reEvaluateCriteria(List<TaskStruct> globalListTasks, Calendar timeCriteria, String criteria)
		{
			if(criteria == null || timeCriteria == null)
				return;
			this.listTasks.clear();
			String timeCriteriaString = sdfOnlyDay.format(timeCriteria.getTime());

			TaskStruct selectedTask = null;
			int a;
			if(displayMode == MODE_VIEW_RELATED)
			{
				a = Integer.parseInt(criteria);
				for(TaskStruct t : globalListTasks)
				{
					if(t.taskID == a)
						selectedTask = t;
				}
			}

			for(TaskStruct t : globalListTasks)
			{
				switch(displayMode)
				{
				case MODE_VIEW_CONTEXT:
					if(t.objectType != TTObject.OBJECT_TYPE_EVENT && criteria.equals(TTObject.getContextFromId(t.context).name))
						this.listTasks.add(t);
					break;
				case MODE_VIEW_CATEGORY:
					if(t.objectType != TTObject.OBJECT_TYPE_TIMEBLOCK && criteria.equals(TTObject.getCategoryFromId(t.category).name))
						this.listTasks.add(t);
					break;
				case MODE_VIEW_CALENDAR:
					if((t.objectType == TTObject.OBJECT_TYPE_EVENT || t.objectType == TTObject.OBJECT_TYPE_TIMEBLOCK) && t.dateStart != null && timeCriteriaString.equals(sdfOnlyDay.format(t.dateStart.getTime())))
						this.listTasks.add(t);  
					if(t.objectType == TTObject.OBJECT_TYPE_TASK && t.dateDue != null && timeCriteriaString.equals(sdfOnlyDay.format(t.dateDue.getTime())))
						this.listTasks.add(t);      		
					break;
				case MODE_VIEW_RELATED:
					if(selectedTask != null && selectedTask.relatedTaskIDs.contains(t.taskID))
						this.listTasks.add(t);
					break;
				}
			}
			origListTasks = new ArrayList<TaskStruct>();
			origListTasks.addAll(this.listTasks);
			sortTasks(prevSortMode);
			super.notifyDataSetInvalidated();
			super.notifyDataSetChanged();
		}



		public void changeUnderlyingData(List<TaskStruct> newTasksToShow)
		{
			this.listTasks.clear();
			this.listTasks.addAll(newTasksToShow);
			sortTasks(prevSortMode);
			super.notifyDataSetInvalidated();
			super.notifyDataSetChanged();
		}

		public void resetUnderlyingData()
		{
			changeUnderlyingData(origListTasks);
		}

		public void sortTasks(int mode)
		{
			switch(mode)
			{
			case MODE_SORT_NAME:
				Collections.sort(listTasks, new Comparator<TaskStruct>(){

					@Override
					public int compare(TaskStruct lhs, TaskStruct rhs) {
						return lhs.name.toLowerCase().compareTo(rhs.name.toLowerCase());
					}
				});
				break;
			case MODE_SORT_TIME:
				Collections.sort(listTasks, new Comparator<TaskStruct>(){

					@Override
					public int compare(TaskStruct lhs, TaskStruct rhs) {
						Calendar lhsTime = lhs.objectType == TTObject.OBJECT_TYPE_TASK ? lhs.dateDue : lhs.dateStart;
						Calendar rhsTime = rhs.objectType == TTObject.OBJECT_TYPE_TASK ? rhs.dateDue : rhs.dateStart;
						
						if(lhsTime == null && rhsTime == null)
							return 0;
						if(lhsTime == null)
							return -1;
						if(rhsTime == null)
							return 1;
						
						return lhsTime.compareTo(rhsTime);
					}
				});
				break;
			case MODE_SORT_CONTEXT:
				Collections.sort(listTasks, new Comparator<TaskStruct>(){

					@Override
					public int compare(TaskStruct lhs, TaskStruct rhs) {
						return ((Integer) lhs.context).compareTo(rhs.context);
					}
				});
				break;
			case MODE_SORT_CATEGORY:
				Collections.sort(listTasks, new Comparator<TaskStruct>(){

					@Override
					public int compare(TaskStruct lhs, TaskStruct rhs) {
						return ((Integer) lhs.category).compareTo(rhs.category);
					}
				});
				break;
			}
			super.notifyDataSetInvalidated();
			super.notifyDataSetChanged();
			prevSortMode = mode;
		}

		public List<TaskStruct> filterTasks(String criteria)
		{
			criteria = criteria.toLowerCase().trim();
			List<TaskStruct> newTasks = new ArrayList<TaskStruct>();
			for(TaskStruct t : origListTasks)
			{
				TTCategory cat = TTObject.getCategoryFromId(t.category);
				TTContext con = TTObject.getContextFromId(t.context);
				
				if((t.name != null && t.name.toLowerCase().contains(criteria)) || 
						(cat != null && cat.name.toLowerCase().contains(criteria)) || 
						(con != null && con.name.toLowerCase().contains(criteria)) || 
						(t.location != null && t.location.toLowerCase().contains(criteria)))
					newTasks.add(t);
			}
			return newTasks;
		}

		@Override
		public boolean hasStableIds() {
			return false;
		}

		@Override
		public int getCount() {
			return listTasks.size();
		}

		@Override
		public Object getItem(int position) {
			return listTasks.get(position);
		}

		@Override
		public long getItemId(int position) {
			return position;
		}

		@Override
		public View getView(int position, View convertView, ViewGroup parent) {
			View groupView = mInflater.inflate(R.layout.fragment_task_smallview, parent, false);

			TaskStruct task = (TaskStruct) getItem(position);
			
			String location = task.location == null ? "None" : task.location;
			String dateStart = task.dateStart == null ? "No date specified" : sdf.format(task.dateStart.getTime());
			String dateDue = task.dateDue == null ? "No date specified" : sdf.format(task.dateDue.getTime());
			
			((TextView) groupView.findViewById(R.id.taskName)).setText
			(Html.fromHtml("<b>" + task.name + "</b>"));
			
			if(task.objectType == TTObject.OBJECT_TYPE_TASK && task.completed)
				((TextView) groupView.findViewById(R.id.taskName)).setPaintFlags(((TextView) groupView.findViewById(R.id.taskName)).getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
			
			if(task.objectType == TTObject.OBJECT_TYPE_EVENT)
			{
				LinearLayout l = (LinearLayout) groupView.findViewById(R.id.smallTaskViewLL);
				String color = "#" + TTObject.getCategoryFromId(task.category).color;
				
				if(color.length() == 7)
					l.setBackgroundColor(Color.parseColor(color) & 0x88FFFFFF);
				
				((TextView) groupView.findViewById(R.id.taskLocation)).setText
				(Html.fromHtml("<b>" + "Location: " + "</b>" + (location == null || location.length() == 0 ? "None" : location)));
				((TextView) groupView.findViewById(R.id.taskDue)).setText
				(Html.fromHtml("<b>" + "Starts: " + "</b>" + dateStart));
			}
			else if(task.objectType == TTObject.OBJECT_TYPE_TASK)
			{
				
				((TextView) groupView.findViewById(R.id.taskLocation)).setText
				(Html.fromHtml("<b>" + "Location: " + "</b>" + (location == null || location.length() == 0 ? "None" : location)));
				((TextView) groupView.findViewById(R.id.taskDue)).setText
				(Html.fromHtml("<b>" + "Due: " + "</b>" + dateDue));
			}
			else if(task.objectType == TTObject.OBJECT_TYPE_TIMEBLOCK)
			{
				LinearLayout l = (LinearLayout) groupView.findViewById(R.id.smallTaskViewLL);
				String color = "#" + TTObject.getContextFromId(task.context).color;
				
				if(color.length() == 7)
					l.setBackgroundColor(Color.parseColor(color) & 0x88FFFFFF);
				
				((TextView) groupView.findViewById(R.id.taskLocation)).setText
				(Html.fromHtml("<b>" + "Starts: " + "</b>" + dateStart));
				((TextView) groupView.findViewById(R.id.taskDue)).setText
				(Html.fromHtml("<b>" + "Ends: " + "</b>" + dateDue));
			}

			return groupView;
		}
	}

	//fix switching back to an already created fragment
	@Override
	public void onDetach() {
		super.onDetach();

		try {
			Field childFragmentManager = android.support.v4.app.Fragment.class.getDeclaredField("mChildFragmentManager");
			childFragmentManager.setAccessible(true);
			childFragmentManager.set(this, null);

		} catch (NoSuchFieldException e) {
			throw new RuntimeException(e);
		} catch (IllegalAccessException e) {
			throw new RuntimeException(e);
		}
	}

}

