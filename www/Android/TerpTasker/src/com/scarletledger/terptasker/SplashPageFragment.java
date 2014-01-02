package com.scarletledger.terptasker;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.cookie.Cookie;
import org.apache.http.impl.client.BasicCookieStore;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;

import com.google.gson.Gson;

import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentActivity;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

public class SplashPageFragment extends Fragment {

	public static final String NUM_SCREEN = "title";
	public static final String SAVED_HASHED_PASSWORD = "!SAVED_PASSWORD!";

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {

		if(getArguments().getInt(NUM_SCREEN) == 4)
		{
			final View rootView = inflater.inflate(R.layout.fragment_login_dummy,
					container, false);

			final EditText uNameInput = (EditText) rootView.findViewById(R.id.email_Field);
			final EditText passInput = (EditText) rootView.findViewById(R.id.password_Field);
			final CheckBox rememberMeCheck = (CheckBox) rootView.findViewById(R.id.rememberLoginCheckbox);
			final SharedPreferences prefs = this.getActivity().getApplicationContext().getSharedPreferences(RefreshService.SHARED_PREFS, 0);
			final Editor edit = prefs.edit();
			final Context context= this.getActivity();

			//Populate editTexts - if saved info exists, set handlers to detect if user changes fields
			final String userName;
			String password;

			userName = prefs.getString(SplashActivity.savedEmailField, "");
			password = prefs.getString(SplashActivity.savedPasswordField, "");

			if(!userName.equals("") && !password.equals(""))
			{
				uNameInput.setText(userName);
				passInput.setHint("Saved");
				passInput.setOnClickListener(new OnClickListener(){
					@Override
					public void onClick(View v) {
						passInput.setHint("Password");
					}
				});
				uNameInput.setOnClickListener(new OnClickListener(){
					@Override
					public void onClick(View v) {
						if(uNameInput.getText().equals(userName))
						uNameInput.setText("");
						passInput.setHint("Password");
					}
				});

				rememberMeCheck.setChecked(true);
			}
			else
				rememberMeCheck.setChecked(false);

			Button loginButton = (Button) rootView.findViewById(R.id.login_button);
			loginButton.setOnClickListener(new View.OnClickListener(){
				public void onClick(final View v) {
					final ProgressDialog progress = ProgressDialog.show(v.getContext(), "Please wait..",
							"Logging you into Terp Tasker and syncing the latest data", true);
						
					new Thread(new Runnable() {
						@Override
						public void run()
						{
							try {
								Thread.sleep(500);
							} catch (Exception e1) {}
							
							String userName = uNameInput.getText().toString();
							String password = null;

							if(!passInput.getHint().toString().equals("Saved"))
							{
								try {
									password = returnSHA512(passInput.getText().toString());
								} catch (NoSuchAlgorithmException e) {}
							}
							else
								password = prefs.getString(SplashActivity.savedPasswordField, "");

							final FragmentActivity context = getActivity();
							if(postLoginData(context, userName, password))
							{
								if(!prefs.contains(SplashActivity.hasSeenTutorial) || !prefs.getBoolean(SplashActivity.hasSeenTutorial, false))
									edit.putBoolean(SplashActivity.hasSeenTutorial, true);

								//Store latest login information, if requested
								if(rememberMeCheck.isChecked())
								{
									edit.putBoolean("PERSIST_USER", true);
								}
								else
								{
									edit.putBoolean("PERSIST_USER", false);
								}
								
								edit.putString(SplashActivity.savedEmailField, userName);
								edit.putString(SplashActivity.savedPasswordField, password);
								
								edit.commit();
								
								RefreshService.setCurrentUserName(context, userName);
								RefreshService.syncFromActivity(context, progress, 0, 0, false, true);
							}
							else
							{
								//Show fail toast - return
								progress.dismiss();					   				
								context.runOnUiThread(new Runnable() {
									public void run() {
										Toast.makeText(context, "Login failed. Please try again.", Toast.LENGTH_SHORT).show();
									}
								});
							} 	
						}
					}).start();
				}
			});
			return rootView;

		}

		View rootView = inflater.inflate(R.layout.fragment_splash_dummy,
				container, false);
		TextView dummyTextView = (TextView) rootView
				.findViewById(R.id.splash_text);
		ImageView imageView = (ImageView) rootView.findViewById(R.id.imageView1);

		imageView.setImageResource(R.drawable.logo_big);
		dummyTextView.setText("Something broke.");

		switch(getArguments().getInt(NUM_SCREEN))
		{
		case 1: dummyTextView.setText(R.string.SplashPageOne); break;
		case 2: dummyTextView.setText(R.string.SplashPageTwo); 
				imageView.setImageResource(R.drawable.computer_icon); 
				break;
		case 3: dummyTextView.setText(R.string.SplashPageThree);
				imageView.setImageResource(R.drawable.swipe_left_demo); 
				break;
		default: dummyTextView.setText("Something broke."); break;

		}

		return rootView;
	}
	
	@Override
	public void onPause()
	{
		getActivity().overridePendingTransition(R.anim.slide_in_left, R.anim.slide_out_right);
		super.onPause();
	}

	public static Bundle createBundle(int value) {
		Bundle bundle = new Bundle();
		bundle.putInt(NUM_SCREEN, value);
		return bundle;
	}

	public static boolean postLoginData(Context context, String userName, String hashedPass) {
		ExecutorService pool = Executors.newFixedThreadPool(1);
		Callable<Boolean> loginTask = new loginCallable(userName, hashedPass, context);
		Future<Boolean> result = pool.submit(loginTask);
		
		try {
			return result.get();
		} catch (Exception e){
			return false;
		}
		
	}
	
	public static class loginCallable implements Callable<Boolean>
	{
		String userName;
		String hashedPass;
		Context context;
		
		public loginCallable(String u, String hP, Context context)
		{
			userName = u;
			hashedPass = hP;
			this.context = context;
		}

		@Override
		public Boolean call() throws Exception {
			DefaultHttpClient httpclient = new DefaultHttpClient();
			
			HttpPost httppost = new HttpPost(
					"http://vulgarity.cs.umd.edu/Login/process_android.php");
						
			try {
				List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
				nameValuePairs.add(new BasicNameValuePair("email", userName));
				nameValuePairs.add(new BasicNameValuePair("password", hashedPass));
				httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

				// Execute HTTP Post Request
				HttpResponse response = httpclient.execute(httppost);
				
				for(Cookie c : httpclient.getCookieStore().getCookies())
				{
					if(c.getName().equals("PHPSESSID"))
					{
						final SharedPreferences prefs = context.getApplicationContext().
								getSharedPreferences(RefreshService.SHARED_PREFS, 0);
						Gson gson = new Gson();
						prefs.edit().putString("PHPSESSID", c.getValue()).commit();
					}
				}
				String result = inputStreamToString(response.getEntity().getContent()).toString();
				return !result.contains("Login failed");

			} catch (ClientProtocolException e) {
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			}
			return false;
		}
		
	}

	private String returnSHA512(String password) throws NoSuchAlgorithmException {
		MessageDigest md = MessageDigest.getInstance("SHA-512");
		md.update(password.getBytes());
		byte byteData[] = md.digest();
		StringBuffer sb = new StringBuffer();
		for (int i = 0; i < byteData.length; i++) {
			sb.append(Integer.toString((byteData[i] & 0xff) + 0x100, 16)
					.substring(1));
		}

		StringBuffer hexString = new StringBuffer();
		for (int i = 0; i < byteData.length; i++) {
			String hex = Integer.toHexString(0xff & byteData[i]);
			if (hex.length() == 1) {
				hexString.append('0');
			}
			hexString.append(hex);
		}
		return hexString.toString();
	}

	public static StringBuilder inputStreamToString(InputStream is) {
		String line = "";
		StringBuilder total = new StringBuilder();
		// Wrap a BufferedReader around the InputStream
		BufferedReader rd = new BufferedReader(new InputStreamReader(is));
		// Read response until the end
		try {
			while ((line = rd.readLine()) != null) {
				total.append(line);
			}
		} catch (IOException e) {
			e.printStackTrace();
		}
		// Return full string
		return total;
	}

}
