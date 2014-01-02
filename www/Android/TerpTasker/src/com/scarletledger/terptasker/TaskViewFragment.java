package com.scarletledger.terptasker;

import java.util.ArrayList;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.actionbarsherlock.app.SherlockListFragment;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuInflater;
import com.actionbarsherlock.view.MenuItem;
import com.scarletledger.terptasker.MainTaskListFragment.TaskListAdapter;
import com.scarletledger.terptasker.R.anim;
import com.scarletledger.terptasker.R.layout;
import com.scarletledger.terptasker.TTObject.TaskStruct;

public class TaskViewFragment extends SherlockListFragment{
	View myView;
	int viewMode;
	int position;
	String criteria;
	MainTaskListFragment.TaskListAdapter mAdapter;

	static TaskViewFragment newInstance(String criteria, int mode, int position) {
		TaskViewFragment f = new TaskViewFragment();
		f.criteria = criteria;
		f.viewMode = mode;
		f.position = position;
		return f;
	}

	@Override
	public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
		ActionBarMethods.handleActionBar(menu, inflater, this);
	}

	@Override
	public void onPrepareOptionsMenu(Menu menu)
	{
		ActionBarMethods.handleReselectSort(menu, mAdapter);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		ActionBarMethods.handleSortSelection(item, mAdapter);
		return true;
	}

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
	}

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
		myView = inflater.inflate(R.layout.fragment_pager_list, container, false);

		mAdapter = new MainTaskListFragment.TaskListAdapter(myView.getContext(), 
				new ArrayList<TaskStruct>(MainProgramActivity.globalTasks), viewMode, criteria);

		setListAdapter(mAdapter);			
		if(viewMode != MainTaskListFragment.MODE_VIEW_CALENDAR)
			setHasOptionsMenu(true);

		mAdapter.prevSortMode = MainTaskListFragment.MODE_SORT_TIME;
		mAdapter.sortTasks(MainTaskListFragment.MODE_SORT_TIME);

		return myView;
	}

	@Override
	public void onActivityCreated(Bundle savedInstanceState)
	{
		super.onActivityCreated(savedInstanceState);
		this.getListView().setSelection(mAdapter.currTimeTaskIndex());
	}

	//When a task is clicked - open fullscreen view
	@Override
	public void onListItemClick(ListView l, View v, int position, long id) {
		Intent i = new Intent(v.getContext().getApplicationContext(), TaskViewActivity.class);
		Bundle bundle = new Bundle();
		bundle.putParcelable("Task", (TaskStruct) l.getAdapter().getItem(position));
		i.putExtras(bundle);
		startActivity(i);
		this.getActivity().overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
	}


}
