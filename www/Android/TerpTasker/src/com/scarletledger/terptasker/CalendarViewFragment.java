package com.scarletledger.terptasker;

import java.util.Calendar;
import java.util.HashMap;

import android.os.Bundle;
import android.support.v4.app.FragmentTransaction;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.actionbarsherlock.app.SherlockFragment;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuInflater;
import com.actionbarsherlock.view.MenuItem;
import com.scarletledger.terptasker.CalendarView.OnDateChangeListener;
import com.scarletledger.terptasker.MainTaskListFragment.TaskListAdapter;
import com.scarletledger.terptasker.TTObject.TaskStruct;

public class CalendarViewFragment extends SherlockFragment{
	View mRootView;
	TaskViewFragment mTaskFrag;
	int position;

	static CalendarViewFragment newInstance(int position) {
		CalendarViewFragment f = new CalendarViewFragment();
		Bundle bundle = new Bundle();
		bundle.putInt("CAL_VIEW", position);
		f.setArguments(bundle);
		f.position = position;
		return f;
	}

	@Override
	public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
		ActionBarMethods.handleActionBar(menu, inflater, mTaskFrag);
	}

	@Override
	public void onPrepareOptionsMenu(Menu menu)
	{
		ActionBarMethods.handleReselectSort(menu, (TaskListAdapter) mTaskFrag.mAdapter);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		ActionBarMethods.handleSortSelection(item, (TaskListAdapter) mTaskFrag.mAdapter);
		return true;
	}

	@Override
	public void onActivityCreated(Bundle savedInstanceState)
	{
		if(mTaskFrag == null)
			mTaskFrag = TaskViewFragment.newInstance(Calendar.getInstance().toString(), MainTaskListFragment.MODE_VIEW_CALENDAR, position);
		setHasOptionsMenu(true);
		super.onActivityCreated(savedInstanceState);
	}

	@Override
	public View onCreateView(LayoutInflater inflater, final ViewGroup container,
			Bundle savedInstanceState) {
		com.scarletledger.terptasker.CalendarView cv = null;
		FragmentTransaction ft = getFragmentManager().beginTransaction();

		//Analyze tasks to populate calendar colors
		HashMap<String, Integer> eventsOnDays = new HashMap<String, Integer>();
		for(TaskStruct t : MainProgramActivity.globalTasks)
		{
			Calendar timeToCheck = t.objectType == TTObject.OBJECT_TYPE_TASK ? t.dateDue : t.dateStart;
			
			if(timeToCheck == null)
				continue;
			
			String currDay = MainTaskListFragment.sdfOnlyDay.format(timeToCheck.getTime());
			//Assumes strings are already formatted to "MM/dd/yyyy"
			if(!eventsOnDays.containsKey(currDay))
				eventsOnDays.put(currDay, 1);
			else
				eventsOnDays.put(currDay, eventsOnDays.get(currDay) + 1);
		}

		if(mTaskFrag == null)
			mTaskFrag = TaskViewFragment.newInstance(Calendar.getInstance().toString(), MainTaskListFragment.MODE_VIEW_CALENDAR, position);

		switch(getArguments().getInt("CAL_VIEW"))
		{
		case MainTaskListFragment.CALENDAR_WEEK_VIEW:
			mRootView = inflater.inflate(R.layout.calendar_week, container, false);
			cv = (com.scarletledger.terptasker.CalendarView) mRootView.findViewById(R.id.calendarView1);
			cv.setShownWeekCount(1);
			cv.numEventsOnDay.clear();
			cv.numEventsOnDay.putAll(eventsOnDays);
			ft.replace(R.id.content_frame_cal_week, mTaskFrag);
			break;
		case MainTaskListFragment.CALENDAR_MONTH_VIEW:
			mRootView = inflater.inflate(R.layout.calendar_month, container, false);
			cv = (com.scarletledger.terptasker.CalendarView) mRootView.findViewById(R.id.calendarView1);
			cv.numEventsOnDay.clear();
			cv.numEventsOnDay.putAll(eventsOnDays);
			ft.replace(R.id.content_frame_cal_month, mTaskFrag);
			break;
		}
		cv.setShowWeekNumber(false);
		cv.setOnDateChangeListener(new OnDateChangeListener(){

			@Override
			public void onSelectedDayChange(CalendarView view, int year,
					int month, int dayOfMonth) {
				Calendar c = Calendar.getInstance();
				c.set(year, month, dayOfMonth);
				mTaskFrag.criteria = MainTaskListFragment.sdfOnlyDay.format(c.getTime());
				((TaskListAdapter)mTaskFrag.mAdapter).criteria = mTaskFrag.criteria;
				((TaskListAdapter)mTaskFrag.mAdapter).reEvaluateCriteria(MainProgramActivity.globalTasks, c, mTaskFrag.criteria);
			}

		});
		cv.invalidate();
		cv.invalidateAllWeekViews();

		Calendar c1 = Calendar.getInstance();
		mTaskFrag.criteria = MainTaskListFragment.sdfOnlyDay.format(c1.getTime());
		if(mTaskFrag.mAdapter != null)
		{
			((TaskListAdapter)mTaskFrag.mAdapter).criteria = mTaskFrag.criteria;
			((TaskListAdapter)mTaskFrag.mAdapter).reEvaluateCriteria(MainProgramActivity.globalTasks, c1, mTaskFrag.criteria);
		}

		ft.commit();

		return mRootView;
	}
}