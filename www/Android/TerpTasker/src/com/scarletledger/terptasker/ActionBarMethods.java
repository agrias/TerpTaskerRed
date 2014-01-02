package com.scarletledger.terptasker;

import java.util.Calendar;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.DialogInterface.OnClickListener;
import android.content.Intent;
import android.content.SharedPreferences;
import android.support.v4.app.FragmentActivity;

import com.actionbarsherlock.app.SherlockFragmentActivity;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuInflater;
import com.actionbarsherlock.view.MenuItem;
import com.actionbarsherlock.view.MenuItem.OnActionExpandListener;
import com.actionbarsherlock.view.MenuItem.OnMenuItemClickListener;
import com.actionbarsherlock.widget.SearchView;
import com.actionbarsherlock.widget.SearchView.OnQueryTextListener;
import com.scarletledger.terptasker.ConversationViewFragment.ConversationListAdapter;
import com.scarletledger.terptasker.MainTaskListFragment.TaskListAdapter;
import com.scarletledger.terptasker.R.id;

public class ActionBarMethods {

	public static void handleActionBar(Menu menu, MenuInflater inflater, final TaskViewFragment tvf)
	{
		MenuItem syncItem = menu.findItem(R.id.sync_action);

		if(syncItem != null)
			syncItem.setOnMenuItemClickListener(new OnMenuItemClickListener(){

				@Override
				public boolean onMenuItemClick(MenuItem item) {

					Calendar now = Calendar.getInstance();
					SharedPreferences prefs = tvf.getActivity().getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
					now.setTimeInMillis(prefs.getLong(RefreshService.getCurrentUserName(tvf.getSherlockActivity()) + RefreshService.TIME_OF_LAST_SYNC, System.currentTimeMillis()));
					
					AlertDialog.Builder builder = null;
					builder = new AlertDialog.Builder(tvf.getActivity());
					builder.setMessage("Last sync: " + MainTaskListFragment.sdf.format(now.getTime()))
					.setTitle("Sync latest task information from Terp Tasker?");

					final SherlockFragmentActivity currContext = tvf.getSherlockActivity();

					builder.setPositiveButton("Yes", new DialogInterface.OnClickListener() {
						public void onClick(DialogInterface dialog, int id) {
							final ProgressDialog progress = new ProgressDialog(currContext);
							progress.setTitle("Please wait..");
							progress.setMessage("Syncing the latest data with Terp Tasker..");
							progress.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
							progress.show();
							
							new Thread()
							{
								public void run()
								{
									int position = 0;
									switch(tvf.viewMode)
									{
									case MainTaskListFragment.MODE_VIEW_CALENDAR: position = 0; break;
									case MainTaskListFragment.MODE_VIEW_CATEGORY: position = 1; break;
									case MainTaskListFragment.MODE_VIEW_CONTEXT: position = 2; break;
									}
									
									RefreshService.syncFromActivity(currContext, progress, position, tvf.position, true, false);

								}
							}.run();
							
						}
					});
					builder.setNegativeButton("No", new DialogInterface.OnClickListener() {
						public void onClick(DialogInterface dialog, int id) {
						}
					});

					builder.create().show();

					return false;
				}

			});

		MenuItem searchItem = menu.findItem(R.id.search_action);

		if(searchItem == null)
			return;

		searchItem.collapseActionView();
		SearchView searchView = (SearchView) searchItem.getActionView();

		searchView.setOnQueryTextListener(new OnQueryTextListener() {
			@Override
			public boolean onQueryTextSubmit(String query) {
				return false;
			}

			@Override
			public boolean onQueryTextChange(String newText) {  
				if(tvf.mAdapter == null)
					return false;
				if(newText.length() > 0)
					((MainTaskListFragment.TaskListAdapter) tvf.mAdapter).changeUnderlyingData(((MainTaskListFragment.TaskListAdapter) tvf.mAdapter).filterTasks(newText));
				else
					((MainTaskListFragment.TaskListAdapter) tvf.mAdapter).resetUnderlyingData();
				return false;
			}
		});

		searchItem.setOnActionExpandListener(new OnActionExpandListener(){

			@Override
			public boolean onMenuItemActionExpand(MenuItem item) {
				return true;
			}

			@Override
			public boolean onMenuItemActionCollapse(MenuItem item) {
				((MainTaskListFragment.TaskListAdapter) tvf.mAdapter).resetUnderlyingData();
				return true;
			}
		});
	}

	//On returning to a view with sort option already selected
	public static void handleReselectSort(Menu menu, final MainTaskListFragment.TaskListAdapter mAdapter)
	{
		if(mAdapter != null && menu != null)
			switch(mAdapter.prevSortMode)
			{
			case MainTaskListFragment.MODE_SORT_NAME:
				if(menu.findItem(R.id.menuSortTitle) != null) menu.findItem(R.id.menuSortTitle).setChecked(true);
				break;
			case MainTaskListFragment.MODE_SORT_TIME:
				if(menu.findItem(R.id.menuSortTime) != null) menu.findItem(R.id.menuSortTime).setChecked(true);
				break;
			case MainTaskListFragment.MODE_SORT_CONTEXT:
				if(menu.findItem(R.id.menuSortContext) != null) menu.findItem(R.id.menuSortContext).setChecked(true);
				break;
			case MainTaskListFragment.MODE_SORT_CATEGORY:
				if(menu.findItem(R.id.menuSortCategory) != null) menu.findItem(R.id.menuSortCategory).setChecked(true);
				break;
			}
	}

	public static void handleSortSelection(MenuItem item, final MainTaskListFragment.TaskListAdapter mAdapter)
	{
		if(mAdapter != null)
		{
			switch(item.getItemId())
			{
			case R.id.menuSortTitle:
				mAdapter.sortTasks(MainTaskListFragment.MODE_SORT_NAME);
				break;
			case R.id.menuSortTime:
				mAdapter.sortTasks(MainTaskListFragment.MODE_SORT_TIME);
				break;
			case R.id.menuSortCategory:
				mAdapter.sortTasks(MainTaskListFragment.MODE_SORT_CATEGORY);
				break;
			case R.id.menuSortContext:
				mAdapter.sortTasks(MainTaskListFragment.MODE_SORT_CONTEXT);
				break;
			}
			item.setChecked(true);
		}
	}

	public static void handleConversationActionBar(Menu menu, final Context context, final int position, final ConversationViewFragment.ConversationListAdapter mAdapter) {
		MenuItem syncItem = menu.findItem(R.id.sync_action);

		if(syncItem != null)
			syncItem.setOnMenuItemClickListener(new OnMenuItemClickListener(){

				@Override
				public boolean onMenuItemClick(MenuItem item) {
					Calendar now = Calendar.getInstance();
					SharedPreferences prefs = context.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
					now.setTimeInMillis(prefs.getLong(RefreshService.getCurrentUserName(context) + RefreshService.TIME_OF_LAST_SYNC, System.currentTimeMillis()));
					
					AlertDialog.Builder builder = null;
					builder = new AlertDialog.Builder(context);
					builder.setMessage("Last sync: " + MainTaskListFragment.sdf.format(now.getTime()))
					.setTitle("Sync latest calls, contacts, and texts with Terp Tasker?");

					builder.setPositiveButton("Yes", new DialogInterface.OnClickListener() {
						public void onClick(DialogInterface dialog, int id) {
							final ProgressDialog progress = new ProgressDialog(context);
							progress.setTitle("Please wait..");
							progress.setMessage("Syncing the latest data with Terp Tasker..");
							progress.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
							progress.show();

							new Thread()
							{
								public void run()
								{
									RefreshService.syncContactSMSCalls(context, progress);
									progress.dismiss();

									Intent launchIntent = new Intent(context, MainProgramActivity.class);
									launchIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
									//launchIntent.setAction(RefreshService.NO_SERVICE_LAUNCH);
									launchIntent.putExtra(MainTaskListFragment.GO_TO_SIDEMENU, 3);
									launchIntent.putExtra(MainTaskListFragment.GO_TO_TAB, position);
									context.startActivity(launchIntent);
									((Activity) context).finish();
								}
							}.start();

						}
					});
					builder.setNegativeButton("No", new DialogInterface.OnClickListener() {
						public void onClick(DialogInterface dialog, int id) {
						}
					});

					builder.create().show();
					return false;
				}

			});

		MenuItem searchItem = menu.findItem(R.id.search_action);

		if(searchItem == null)
			return;

		searchItem.collapseActionView();
		SearchView searchView = (SearchView) searchItem.getActionView();

		searchView.setOnQueryTextListener(new OnQueryTextListener() {
			@Override
			public boolean onQueryTextSubmit(String query) {
				return false;
			}

			@Override
			public boolean onQueryTextChange(String newText) {  
				if(mAdapter == null)
					return false;
				if(newText.length() > 0)
					mAdapter.changeUnderlyingData(mAdapter.filterTasks(newText));
				else
					mAdapter.resetUnderlyingData();
				return false;
			}
		});

		searchItem.setOnActionExpandListener(new OnActionExpandListener(){

			@Override
			public boolean onMenuItemActionExpand(MenuItem item) {
				return true;
			}

			@Override
			public boolean onMenuItemActionCollapse(MenuItem item) {
				mAdapter.resetUnderlyingData();
				return true;
			}
		});
	}

}
