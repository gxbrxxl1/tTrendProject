import sys

def check_package(package_name):
    try:
        __import__(package_name)
        print(f"✓ {package_name} is installed")
        return True
    except ImportError as e:
        print(f"✗ {package_name} is NOT installed: {str(e)}")
        return False

# List of required packages
required_packages = [
    'google.generativeai',
    'pandas',
    'googleapiclient',
    'pytrends'
]

all_installed = True
for package in required_packages:
    if not check_package(package):
        all_installed = False

if all_installed:
    print("\nAll dependencies are installed correctly!")
else:
    print("\nSome dependencies are missing. Please run:")
    print("pip install -r requirements.txt") 