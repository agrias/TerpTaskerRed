package com.scarletledger.terptasker;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.provider.ContactsContract;
import android.text.Html;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.TextView;

import com.actionbarsherlock.app.SherlockListFragment;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuInflater;
import com.actionbarsherlock.view.MenuItem;
import com.scarletledger.terptasker.MainTaskListFragment.TaskListAdapter;
import com.scarletledger.terptasker.TTObject.TTCategory;
import com.scarletledger.terptasker.TTObject.TTConversation;
import com.scarletledger.terptasker.TTObject.TaskStruct;

public class ConversationViewFragment extends SherlockListFragment {
	View myView;
	String criteria;
	int position;
	ConversationListAdapter mAdapter;

	static ConversationViewFragment newInstance(String criteria, int position) {
		ConversationViewFragment f = new ConversationViewFragment();
		f.criteria = criteria;
		f.position = position;
		return f;
	}

	@Override
	public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
		ActionBarMethods.handleConversationActionBar(menu, this.getActivity(), position, mAdapter);
	}

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
	}

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
		myView = inflater.inflate(R.layout.fragment_pager_list, container, false);

		mAdapter = new ConversationListAdapter(myView.getContext(), MainProgramActivity.globalConversations, criteria);

		setListAdapter(mAdapter);			
		setHasOptionsMenu(true);

		return myView;
	}

	//When a task is clicked - open fullscreen view
	@Override
	public void onListItemClick(ListView l, View v, int position, long id) {
		TTConversation conv = (TTConversation) mAdapter.getItem(position);
		Intent intent;
		switch(conv.type)
		{
		case TTConversation.TYPE_CALL_HISTORY:
			String uri = "tel:" + conv.phoneNum.replaceAll("[^0-9|\\+]", "");
			intent = new Intent(Intent.ACTION_CALL, Uri.parse(uri));
			startActivity(intent);
			break;
		case TTConversation.TYPE_CONTACT:
			intent = new Intent(); 
			intent.setAction(ContactsContract.Intents.SHOW_OR_CREATE_CONTACT); 
			intent.setData(Uri.fromParts("tel", conv.phoneNum.replaceAll("[^0-9|\\+]", ""), null));
			startActivity(intent);
			break;
		case TTConversation.TYPE_TEXT_MESSAGE:
			intent = new Intent(Intent.ACTION_VIEW);
			intent.setData(Uri.parse("sms:" + conv.phoneNum.replaceAll("[^0-9|\\+]", "")));
			startActivity(intent);
			break;
		}
		this.getActivity().overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
	}


	//Class that displays the main info of a task, can be clicked
	public static class ConversationListAdapter extends BaseAdapter{
		private LayoutInflater mInflater;
		private List<TTConversation> origListConversations;
		private List<TTConversation> listConversations;

		public ConversationListAdapter(Context context, List<TTConversation> globalConversations, String categoryCriteria) {

			mInflater = LayoutInflater.from(context);
			this.listConversations = new ArrayList<TTConversation>();
			
			for(TTConversation c : globalConversations)
			{
				TTCategory cat = TTObject.getCategoryFromId(c.category);
				if(cat != null && cat.name.equals(categoryCriteria))
				{
					listConversations.add(c);
				}
			}
			
			this.origListConversations = new ArrayList<TTConversation>(listConversations);
			super.notifyDataSetInvalidated();
			super.notifyDataSetChanged();
		}

		public void changeUnderlyingData(List<TTConversation> newTasksToShow)
		{
			this.listConversations.clear();
			this.listConversations.addAll(newTasksToShow);
			super.notifyDataSetInvalidated();
			super.notifyDataSetChanged();
		}

		public void resetUnderlyingData()
		{
			changeUnderlyingData(origListConversations);
		}
		
		public List<TTConversation> filterTasks(String criteria)
		{
			criteria = criteria.toLowerCase().trim();
			List<TTConversation> newTasks = new ArrayList<TTConversation>();
			for(TTConversation t : origListConversations)
			{
				if((t.contactName != null && t.contactName.toLowerCase().contains(criteria)) || 
						(t.phoneNum != null && t.phoneNum.toLowerCase().contains(criteria)) || 
							(t.textContent != null && t.textContent.toLowerCase().contains(criteria)) ||
								(t.email != null && t.email.toLowerCase().contains(criteria)))
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
			return listConversations.size();
		}

		@Override
		public Object getItem(int position) {
			return listConversations.get(position);
		}

		@Override
		public long getItemId(int position) {
			return position;
		}

		@Override
		public View getView(int position, View convertView, ViewGroup parent) {
			View groupView = mInflater.inflate(R.layout.fragment_conversation_smallview, parent, false);

			TTConversation conv = (TTConversation) getItem(position);
			LinearLayout ll = (LinearLayout) groupView.findViewById(R.id.conversationLayout);

			String color = "#" + TTObject.getCategoryFromId(conv.category).color;
			
			if(color.length() == 7)
				ll.setBackgroundColor(Color.parseColor(color) & 0x88FFFFFF);

			ImageView icon = (ImageView) groupView.findViewById(R.id.conversationIcon);

			switch(conv.type)
			{
			case TTConversation.TYPE_CALL_HISTORY: icon.setImageResource(R.drawable.conversation_icon_dark); break;
			case TTConversation.TYPE_CONTACT: icon.setImageResource(R.drawable.contact_icon); break;
			case TTConversation.TYPE_TEXT_MESSAGE: icon.setImageResource(R.drawable.text_icon); break;
			}

			TextView name = (TextView) groupView.findViewById(R.id.conversationName);
			name.setText(Html.fromHtml("<b>" + conv.contactName + "</b> " + "<i>(" + conv.phoneNum + ")</i>"));

			TextView det1 = (TextView) groupView.findViewById(R.id.conversationDetail1);

			String time = conv.time == null ? "N/A" : conv.time;
			
			switch(conv.type)
			{
			case TTConversation.TYPE_CALL_HISTORY: det1.setText(Html.fromHtml("<b>Category:</b> " + TTObject.getCategoryFromId(conv.category).name + ", <b>At:</b> " + time)); break;
			case TTConversation.TYPE_CONTACT: det1.setText(Html.fromHtml("<b>Category:</b> " + TTObject.getCategoryFromId(conv.category).name)); break;
			case TTConversation.TYPE_TEXT_MESSAGE: det1.setText(Html.fromHtml("<b>Category:</b> " + TTObject.getCategoryFromId(conv.category).name + ", <b>At:</b> " + time)); break;
			}

			TextView det2 = (TextView) groupView.findViewById(R.id.conversationDetail2);

			switch(conv.type)
			{
			case TTConversation.TYPE_CALL_HISTORY: det2.setText(Html.fromHtml("<b>Duration: </b>" + conv.duration)); break;
			case TTConversation.TYPE_CONTACT: det2.setText(Html.fromHtml("<b>Email: </b>" + conv.email)); break;
			case TTConversation.TYPE_TEXT_MESSAGE: det2.setText(Html.fromHtml("<b>Text: </b>" + conv.textContent)); break;
			}

			return groupView;
		}
	}
}
