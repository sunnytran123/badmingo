#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import time

def test_bot_toggle():
    """Test chức năng toggle bot"""
    base_url = "http://localhost:5000/api"
    test_user = "test_user_123"
    
    print("🔍 Testing Bot Toggle Functionality")
    print("=" * 50)
    
    # Test 1: Gửi tin nhắn bình thường (bot bật)
    print("1️⃣ Testing normal chat (bot enabled)...")
    try:
        response = requests.post(f"{base_url}/chat", json={
            "user_id": test_user,
            "message": "Xin chào"
        }, timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Response: {data.get('response', 'No response')[:100]}...")
        else:
            print(f"❌ HTTP Error: {response.status_code}")
    except Exception as e:
        print(f"❌ Connection Error: {e}")
        return False
    
    # Test 2: Tắt bot cho user
    print("\n2️⃣ Disabling bot for user...")
    try:
        response = requests.post(f"{base_url}/admin/toggle-bot", json={
            "user_id": test_user,
            "bot_disabled": True
        }, timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Toggle result: {data}")
        else:
            print(f"❌ Toggle Error: {response.status_code}")
    except Exception as e:
        print(f"❌ Toggle Error: {e}")
        return False
    
    # Test 3: Gửi tin nhắn khi bot bị tắt
    print("\n3️⃣ Testing chat when bot disabled...")
    try:
        response = requests.post(f"{base_url}/chat", json={
            "user_id": test_user,
            "message": "Bot có còn hoạt động không?"
        }, timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            response_text = data.get('response', '')
            if "Bot hiện đang tắt" in response_text:
                print("✅ Bot correctly disabled - no response")
            else:
                print(f"❌ Bot still responding: {response_text[:100]}...")
        else:
            print(f"❌ HTTP Error: {response.status_code}")
    except Exception as e:
        print(f"❌ Connection Error: {e}")
        return False
    
    # Test 4: Bật lại bot
    print("\n4️⃣ Re-enabling bot...")
    try:
        response = requests.post(f"{base_url}/admin/toggle-bot", json={
            "user_id": test_user,
            "bot_disabled": False
        }, timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Re-enable result: {data}")
        else:
            print(f"❌ Re-enable Error: {response.status_code}")
    except Exception as e:
        print(f"❌ Re-enable Error: {e}")
        return False
    
    # Test 5: Gửi tin nhắn khi bot bật lại
    print("\n5️⃣ Testing chat when bot re-enabled...")
    try:
        response = requests.post(f"{base_url}/chat", json={
            "user_id": test_user,
            "message": "Bot đã hoạt động lại chưa?"
        }, timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            response_text = data.get('response', '')
            if "Bot hiện đang tắt" not in response_text:
                print("✅ Bot correctly re-enabled - responding normally")
            else:
                print(f"❌ Bot still disabled: {response_text[:100]}...")
        else:
            print(f"❌ HTTP Error: {response.status_code}")
    except Exception as e:
        print(f"❌ Connection Error: {e}")
        return False
    
    print("\n" + "=" * 50)
    print("🎉 Bot toggle test completed!")
    return True

if __name__ == "__main__":
    print("🚀 Starting bot toggle test...")
    print("💡 Make sure the Python server is running: cd sv && python chatbot_badminton.py")
    print()
    
    test_bot_toggle()
