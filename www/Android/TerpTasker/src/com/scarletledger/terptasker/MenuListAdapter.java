package com.scarletledger.terptasker;

import android.content.Context;
import android.text.Html;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;
 
//Adapter for sidemenu nav
public class MenuListAdapter extends BaseAdapter {
    private Context mContext;
    private String[] mTabTitles;
    private String[] mTabSubtitles;
    private int[] mTabIcons;
    private LayoutInflater mInflater;
 
    public MenuListAdapter(Context context, String[] title, String[] subtitle,
            int[] icon) {
        this.mContext = context;
        this.mTabTitles = title;
        this.mTabSubtitles = subtitle;
        this.mTabIcons = icon;
    }
 
    @Override
    public int getCount() {
        return mTabTitles.length;
    }
 
    @Override
    public Object getItem(int position) {
        return mTabTitles[position];
    }
 
    @Override
    public long getItemId(int position) {
        return position;
    }
 
    public View getView(int position, View convertView, ViewGroup parent) { 
        mInflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View itemView = mInflater.inflate(R.layout.main_application_drawer_item, parent, false);
 
        TextView txtTitle = (TextView) itemView.findViewById(R.id.title);
        TextView txtSubTitle = (TextView) itemView.findViewById(R.id.subtitle);
 
        ImageView imgIcon = (ImageView) itemView.findViewById(R.id.icon);
 
        txtTitle.setText(Html.fromHtml(mTabTitles[position]));
        txtSubTitle.setText(mTabSubtitles[position]);
 
        imgIcon.setImageResource(mTabIcons[position]);
 
        return itemView;
    }
 
}
