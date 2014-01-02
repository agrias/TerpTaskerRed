package com.scarletledger.terptasker;
 
import com.actionbarsherlock.app.ActionBar;

import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.LayerDrawable;
import android.os.Bundle;
 
public class SplashActivity extends TabSwipeActivity {
	public static final String hasSeenTutorial = "HAS_SEEN_TUTORIAL";
	public static final String savedEmailField = "REMEMBER_EMAIL_FIELD";
	public static final String savedPasswordField = "REMEMBER_PASSWORD_FIELD";
	
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setActionBarColor(this.getSupportActionBar());
		
		if(RefreshService.getCurrentUserName(this) != null)
		{
			Intent i = new Intent(this, MainProgramActivity.class);
			startActivity(i);
			finish();
		}
		
		SharedPreferences prefs = this.getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
		if(prefs.contains(hasSeenTutorial) && prefs.getBoolean(hasSeenTutorial, false))
			addTab( "Tab 4", SplashPageFragment.class, SplashPageFragment.createBundle(4) );
		else
		{
			addTab( "Tab 1", SplashPageFragment.class, SplashPageFragment.createBundle(1) );
			addTab( "Tab 2", SplashPageFragment.class, SplashPageFragment.createBundle(2) );
			addTab( "Tab 3", SplashPageFragment.class, SplashPageFragment.createBundle(3) );
			addTab( "Tab 4", SplashPageFragment.class, SplashPageFragment.createBundle(4) );
		}
		
	}
	
	public static void setActionBarColor(ActionBar a)
	{
		Drawable colorDrawable = new ColorDrawable(Color.parseColor("#3D3242"));
        LayerDrawable ld = new LayerDrawable(new Drawable[] { colorDrawable });
        a.setBackgroundDrawable(ld);
        a.setDisplayShowTitleEnabled(false);
        a.setDisplayShowTitleEnabled(true);
	}
}