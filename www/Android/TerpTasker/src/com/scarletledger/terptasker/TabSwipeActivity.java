package com.scarletledger.terptasker;

import java.util.ArrayList;
import java.util.List;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.view.ViewPager;

import com.actionbarsherlock.app.ActionBar;
import com.actionbarsherlock.app.ActionBar.Tab;
import com.actionbarsherlock.app.ActionBar.TabListener;
import com.actionbarsherlock.app.SherlockFragmentActivity;

import com.viewpagerindicator.*;

public abstract class TabSwipeActivity extends SherlockFragmentActivity {

    private ViewPager mViewPager;
    private TabsAdapter mAdapter;
    private LinePageIndicator mIndicator;

    @Override
    public void onCreate(Bundle savedInstanceState) {
    	/*
    	 * Create the ViewPager and our custom adapter
    	 */
        super.onCreate(savedInstanceState);
        
        setContentView(R.layout.activity_splash);
   
        mViewPager = (ViewPager)findViewById(R.id.viewpager_splash);
        mAdapter = new TabsAdapter( this, mViewPager );
        mViewPager.setAdapter( mAdapter );
        
        mIndicator = (LinePageIndicator)findViewById(R.id.splash_page_indicator);
        mIndicator.setViewPager(mViewPager);
        mIndicator.setOnPageChangeListener(mAdapter);
    }

    protected void addTab(int titleRes, Class fragmentClass, Bundle args ) {
        mAdapter.addTab( getString( titleRes ), fragmentClass, args );
    }

    protected void addTab(CharSequence title, Class fragmentClass, Bundle args ) {
        mAdapter.addTab( title, fragmentClass, args );
    }
    
    public void selectTab(int position)
    {
    	mAdapter.selectTab(position);
    }

    private static class TabsAdapter extends FragmentPagerAdapter implements TabListener, ViewPager.OnPageChangeListener {

    	private final SherlockFragmentActivity activity;
        private final ActionBar actionBar;
        private Tab loginTab;

        /**
         * @param fm
         * @param fragments
         */
        public TabsAdapter(SherlockFragmentActivity activity, ViewPager pager) {
            super(activity.getSupportFragmentManager());
            this.activity = activity;
            this.actionBar = activity.getSupportActionBar();
            this.loginTab = null;

            actionBar.setNavigationMode(ActionBar.NAVIGATION_MODE_STANDARD);
        }

        private static class TabInfo {
            public final Class fragmentClass;
            public final Bundle args;
            public TabInfo(Class fragmentClass,
                    Bundle args) {
                this.fragmentClass = fragmentClass;
                this.args = args;
            }
        }

        private List<TabInfo> mTabs = new ArrayList<TabInfo>();

        public void addTab( CharSequence title, Class fragmentClass, Bundle args ) {
            final TabInfo tabInfo = new TabInfo( fragmentClass, args );

            Tab tab = actionBar.newTab();
            tab.setText( title );
            tab.setTabListener( this );
            tab.setTag( tabInfo );

            mTabs.add( tabInfo );

            actionBar.addTab( tab );
            loginTab = tab;
            notifyDataSetChanged();
        }
        
        public void selectTab(int position)
        {
        	actionBar.selectTab(loginTab);
        }
        

        @Override
        public Fragment getItem(int position) {
            final TabInfo tabInfo = (TabInfo) mTabs.get( position );
            return (Fragment) Fragment.instantiate( activity, tabInfo.fragmentClass.getName(), tabInfo.args );
        }

        @Override
        public int getCount() {
            return mTabs.size();
        }

        public void onTabSelected(Tab tab, FragmentTransaction ft) {
        }

        public void onTabUnselected(Tab tab, FragmentTransaction ft) {
        }

        public void onTabReselected(Tab tab, FragmentTransaction ft) {
        }

		@Override
		public void onPageScrollStateChanged(int arg0) {
		}

		@Override
		public void onPageScrolled(int arg0, float arg1, int arg2) {
		}

		@Override
		public void onPageSelected(int arg0) {
		}
    }
}