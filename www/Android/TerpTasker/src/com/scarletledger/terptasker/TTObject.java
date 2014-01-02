package com.scarletledger.terptasker;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

import android.os.Parcel;
import android.os.Parcelable;

//Unified object to hold task, object, or timeblock that implements parcelable - so that passing info between fragments and activities is easier
public class TTObject 
{
	public static final int OBJECT_TYPE_TASK = 0;
	public static final int OBJECT_TYPE_EVENT = 1;
	public static final int OBJECT_TYPE_TIMEBLOCK = 2;
	
	public static final int NOT_REPEATING = 0;
	public static final int IS_REPEATING = 1;
	public static final int RELATED_REPEATING = 2;
	
	public static final int REPEAT_MODE_DAY = 0;
	public static final int REPEAT_MODE_WEEK = 1;
	public static final int REPEAT_MODE_MONTH = 2;
	public static final int REPEAT_MODE_YEAR = 3;
	
	public static class TaskStruct implements Parcelable
	{
		public int					objectType;
		public int 					taskID;
		public ArrayList<Integer> 	relatedTaskIDs;
		public String 				name;
		public Calendar				dateStart;
		public Calendar 			dateDue;
		public String 				timeToCompletion;
		public String 				location;
		public int	 				context;
		public int	 				category;
		public String				description;
		public int					reminderMinBefore;
		public int					isRepeating;
		public int					repeatMode;
		public int					repeatFreq;
		public Calendar				repeatEnd;
		public boolean				allDay;
		public boolean				completed;
		
		public TaskStruct(int objectType, int taskID, ArrayList<Integer> relatedTaskIDs, String nameTask, Calendar dateStart, Calendar dateDue,
				String timeToCompletion, String location, int context, int category, String description, int reminderMinBefore,
				int isRepeating, int repeatMode, int repeatFreq, Calendar repeatEnd, boolean allDay, boolean completed)
		{
			this.objectType = objectType;
			this.taskID = taskID;
			this.relatedTaskIDs = relatedTaskIDs;
			this.name = nameTask;
			this.dateStart = dateStart;
			this.dateDue = dateDue;
			this.timeToCompletion = timeToCompletion;
			this.location = location;
			this.context = context;
			this.category = category;
			this.description = description;
			this.reminderMinBefore = reminderMinBefore;
			this.isRepeating = isRepeating;
			this.repeatMode = repeatMode;
			this.repeatFreq = repeatFreq;
			this.repeatEnd = repeatEnd;
			this.allDay = allDay;
			this.completed = completed;
		}
		
		public TaskStruct(Parcel in)
		{
			readFromParcel(in);
		}

		@Override
		public int describeContents() {
			return 0;
		}

		@Override
		public void writeToParcel(Parcel dest, int flags) {
			dest.writeInt(objectType);
			dest.writeInt(taskID);
			dest.writeList(relatedTaskIDs);
			dest.writeString(name);
			dest.writeSerializable(dateStart);
			dest.writeSerializable(dateDue);
			dest.writeString(timeToCompletion);
			dest.writeString(location);
			dest.writeInt(context);
			dest.writeInt(category);
			dest.writeString(description);
			dest.writeInt(reminderMinBefore);
			dest.writeByte((byte) (completed ? 1 : 0));
			dest.writeInt(isRepeating);
			
			//Potentially null
			if(isRepeating != NOT_REPEATING)
			{
				dest.writeInt(repeatMode);
				dest.writeInt(repeatFreq);
				dest.writeSerializable(repeatEnd);
				dest.writeByte((byte) (allDay ? 1 : 0));
			}
		}
		
		private void readFromParcel(Parcel in)
		{
			objectType = in.readInt();
			taskID = in.readInt();
			relatedTaskIDs = new ArrayList<Integer>();
			in.readList(relatedTaskIDs, null);
			name = in.readString();
			dateStart = (Calendar) in.readSerializable();
			dateDue = (Calendar) in.readSerializable();
			timeToCompletion = in.readString();
			location = in.readString();
			context = in.readInt();
			category = in.readInt();
			description = in.readString();
			reminderMinBefore = in.readInt();
			completed = in.readByte() == 1;
			isRepeating = in.readInt();
			
			if(isRepeating != NOT_REPEATING)
			{
				repeatMode = in.readInt();
				repeatFreq = in.readInt();
				repeatEnd = (Calendar) in.readSerializable();
				allDay = in.readByte() == 1;
			}
			
		}
		
		public static final Parcelable.Creator<TaskStruct> CREATOR = new Parcelable.Creator<TaskStruct>() {  
		    
	        public TaskStruct createFromParcel(Parcel in) {  
	            return new TaskStruct(in);  
	        }  
	   
	        public TaskStruct[] newArray(int size) {  
	            return new TaskStruct[size];  
	        }  
	          
	    };
	}
	
	public static class TTCategory
	{
		public int categoryID;
		public String name;
		public String description;
		public String color;
		
		public TTCategory(int categoryID, String name, String description, String color)
		{
			this.categoryID = categoryID;
			this.name = name;
			this.description = description;
			this.color = color;
		}
	}
	
	public static class TTContext
	{
		public int contextID;
		public String name;
		public String description;
		public String color;
		
		public TTContext(int contextID, String name, String description, String color)
		{
			this.contextID = contextID;
			this.name = name;
			this.description = description;
			this.color = color;
		}
	}
	
	public static class TTConversation
	{
		public static final int TYPE_CALL_HISTORY = 0;
		public static final int TYPE_CONTACT = 1;
		public static final int TYPE_TEXT_MESSAGE = 2;
		
		public int 			type;
		public String		time;
		public int 			category;
		public String 		phoneNum;
		public String 		contactName;
		public int 			id;
		
		//Calls
		public String		duration;
		
		//Texts
		public String		textContent;
		
		//Contacts
		public String		email;
		
		public TTConversation(int type, String time, int category, String phoneNum, String contactName, int id, String duration, String textContent, String email)
		{
			this.type = type;
			this.time = time;
			this.category = category;
			this.phoneNum = phoneNum;
			this.contactName = contactName;
			this.id = id;
			this.duration = duration;
			this.textContent = textContent;
			this.email = email;
		}
	}
	
	//Format to send contacts, texts, call logs to server
	public static class TTConversationToSend
	{
		public int 			type;
		public long			time;			//format: see MainTaskListFragment.dateTimeFormatter
		public String 		phoneNum = "N/A";
		public String 		contactName = "N/A";
		
		//Calls
		public int 			duration;
		
		//Texts
		public String		textContent = "N/A";
		
		//Contacts
		public String		email = "N/A";
		
	}

	public static TTCategory getCategoryFromId(int id)
	{
		for(TTCategory c : MainProgramActivity.globalCategories)
		{
			if(c.categoryID == id)
				return c;
		}
		return null;
	}

	public static TTContext getContextFromId(int id)
	{
		for(TTContext c : MainProgramActivity.globalContexts)
		{
			if(c.contextID == id)
				return c;
		}
		
		return null;
	}

	//Returns index into category name array if yes
	public static int isTimeBlockCurrentlyOccuring(String[] conNames, List<TaskStruct> globalTasks)
	{
		Calendar now = Calendar.getInstance();
		for(TaskStruct t : globalTasks)
		{
			if(t.objectType == OBJECT_TYPE_TIMEBLOCK)
			{
				if(now.compareTo(t.dateStart) >= 0 && now.compareTo(t.dateDue) <= 0)
				{
					String conName = getContextFromId(t.context).name;
					for(int i = 0; i < conNames.length; i++)
						if(conNames[i].equals(conName))
							return i;
					return -1;
				}
			}
		}
		return -1;
	}

	//Returns index into category name array if yes
	public static int isEventCurrentlyOccuring(String[] catNames, List<TaskStruct> globalTasks)
	{
		Calendar now = Calendar.getInstance();
		for(TaskStruct t : globalTasks)
		{
			if(t.objectType == OBJECT_TYPE_EVENT)
			{
				if(now.compareTo(t.dateStart) >= 0 && now.compareTo(t.dateDue) <= 0)
				{
					String catName = getCategoryFromId(t.category).name;
					for(int i = 0; i < catNames.length; i++)
						if(catNames[i].equals(catName))
							return i;
					return -1;
				}
			}
		}
		return -1;
	}
	
	
	
}
