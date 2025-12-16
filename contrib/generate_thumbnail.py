#!/usr/bin/env python3
import os
import re
import requests
import time
from urllib.parse import urlencode

# ==========================
# CONFIG
# ==========================

# TMDb API key
TMDB_API_KEY = "INSERISCI_LA_TUA_API_KEY_TMBD_QUI"

# Root folder to scan
ROOT_DIR = "/path/to/root/folder"
EXCLUDED_DIRS = {'temp'}

# Supported video file extensions
VIDEO_EXTENSIONS = {".mkv", ".avi", ".mp4", ".mov", ".wmv", ".mpg", ".mpeg"}

# Delay between API requests
API_DELAY_SEC = 0.3

# Required lang
TMDB_LANGUAGE = "it-IT"
TMDB_REGION = "IT"


# ==========================
# UTILITY
# ==========================

def is_video_file(filename: str) -> bool:
    ext = os.path.splitext(filename)[1].lower()
    return ext in VIDEO_EXTENSIONS


def extract_title_and_year(filename: str):
    """
    Try to get (title, year) from file name.

    Es:
      - "The.Matrix.1999.1080p.BluRay.mkv" -> ("The Matrix", 1999)
      - "Inception_2010_1080p.mkv" -> ("Inception", 2010)
      - "La.Casa.Di.Carta.S01E01.2017.mkv" -> ("La Casa Di Carta", 2017)
      - "Avatar.mkv" -> ("Avatar", None)
    """
    name, _ = os.path.splitext(filename)

    # Replace ., _ with spazio
    name_clean = re.sub(r"[._]", " ", name)

    # Search a year (1900-2099)
    match = re.search(r"(19[0-9]{2}|20[0-9]{2})", name_clean)
    year = None
    if match:
        year_str = match.group(0)
        year = int(year_str)
        
        title_part = name_clean[: match.start()]
    else:
        title_part = name_clean

    # Remove tipical quality / format patterns
    garbage_patterns = [
        r"\b1080p\b",
        r"\b720p\b",
        r"\b2160p\b",
        r"\b4k\b",
        r"\bblu[- ]?ray\b",
        r"\bbrrip\b",
        r"\bwebrip\b",
        r"\bdvdrip\b",
        r"\bx264\b",
        r"\bx265\b",
        r"\bhdrip\b",
        r"\bweb[- ]?dl\b",
        r"\bhd[- ]?tv\b",
    ]
    for gp in garbage_patterns:
        title_part = re.sub(gp, " ", title_part, flags=re.IGNORECASE)

    # Remove tipical patterns
    # es: S01E01, S1E1, 1x01, ecc.
    title_part = re.sub(r"S[0-9]{1,2}E[0-9]{1,2}", " ", title_part, flags=re.IGNORECASE)
    title_part = re.sub(r"[0-9]{1,2}x[0-9]{1,2}", " ", title_part, flags=re.IGNORECASE)

    # Multiple spaces -> single space, strip
    title = re.sub(r"\s+", " ", title_part).strip()

    return title, year


def tmdb_request(endpoint: str, params: dict):
    """
    Wrapper for TMDb requests.
    endpoint es: "/search/movie" o "/search/tv"
    """
    if not TMDB_API_KEY:
        print("ERROR: TMDB_API_KEY non set")
        return None

    base_url = "https://api.themoviedb.org/3"
    params = {
        **params,
        "api_key": TMDB_API_KEY,
    }

    url = f"{base_url}{endpoint}?{urlencode(params)}"

    try:
        resp = requests.get(url, timeout=10)
        resp.raise_for_status()
        data = resp.json()
        return data
    except Exception as e:
        print(f"  [ERROR] Request to TMDb failed on {endpoint}: {e}")
        return None


def search_movie_on_tmdb(title: str, year: int | None):
    """
    Search a FILM on TMDb and return first result with poster_path,
    or None if not found.
    """
    params = {
        "query": title,
        "language": TMDB_LANGUAGE,
        "include_adult": False,
    }
    if year:
        params["year"] = year
    if TMDB_REGION:
        params["region"] = TMDB_REGION

    data = tmdb_request("/search/movie", params)
    if not data:
        return None

    results = data.get("results", [])
    if not results:
        return None

    for movie in results:
        if movie.get("poster_path"):
            return movie

    return None


def search_tv_on_tmdb(title: str, year: int | None):
    """
    Search a SERIE TV on TMDb and return first result with poster_path,
    or None if not found.
    """
    params = {
        "query": title,
        "language": TMDB_LANGUAGE,
        "include_adult": False,
    }
    if year:
        params["first_air_date_year"] = year

    data = tmdb_request("/search/tv", params)
    if not data:
        return None

    results = data.get("results", [])
    if not results:
        return None

    for tv in results:
        if tv.get("poster_path"):
            return tv

    return None


def download_poster(poster_path: str, dest_path: str) -> bool:
    """
    Download TMDb poster and save it in dest_path.
    Return True if ok, False otherwise.
    """
    # Can change resolution: w185, w342, w500, original, ecc.
    img_base_url = "https://image.tmdb.org/t/p/w500"
    url = img_base_url + poster_path
    try:
        resp = requests.get(url, stream=True, timeout=10)
        resp.raise_for_status()
        with open(dest_path, "wb") as f:
            for chunk in resp.iter_content(chunk_size=8192):
                if chunk:
                    f.write(chunk)
        return True
    except Exception as e:
        print(f"  [ERROR] Download poster failed: {e}")
        return False


# ==========================
# MAIN
# ==========================

def process_video_file(filepath: str):
    """
    For single video file:
      - generate cover path
      - if not exist, try to:
          1) search as FILM
          2) if fail, search as SERIE TV
      - download poster
    """
    dirpath, filename = os.path.split(filepath)
    name_no_ext, _ = os.path.splitext(filename)
    cover_path = os.path.join(dirpath, name_no_ext + ".jpg")

    if os.path.exists(cover_path):
        print(f"[SKIP] Cover already present: {cover_path}")
        return

    print(f"[VIDEO] {filepath}")

    title, year = extract_title_and_year(filename)
    if not title:
        print("  [WARN] No valid title found in filename.")
        return

    print(f"  Title found: '{title}'  Year: {year if year else 'N/A'}")

    # 1) Try as FILM
    movie = search_movie_on_tmdb(title, year)
    time.sleep(API_DELAY_SEC)

    if movie:
        tmdb_title = movie.get("title", "")
        tmdb_year = (movie.get("release_date", "") or "????")[:4]
        poster_path = movie.get("poster_path")

        print(f"  Match FILM TMDb: '{tmdb_title}' ({tmdb_year})")
        print(f"  Poster path: {poster_path}")

        if poster_path:
            ok = download_poster(poster_path, cover_path)
            if ok:
                print(f"  [OK] Poster FILM saved in: {cover_path}")
            else:
                print("  [ERROR] Unable to save poster file FILM.")
        else:
            print("  [WARN] No poster available for this FILM.")
        return

    # 2) if no FILM found, try as SERIE TV
    print("  No film found, try as SERIE TV...")
    tv = search_tv_on_tmdb(title, year)
    time.sleep(API_DELAY_SEC)

    if tv:
        tmdb_name = tv.get("name", "")
        first_air_date = (tv.get("first_air_date", "") or "????")[:4]
        poster_path = tv.get("poster_path")

        print(f"  Match SERIE TV TMDb: '{tmdb_name}' ({first_air_date})")
        print(f"  Poster path: {poster_path}")

        if poster_path:
            ok = download_poster(poster_path, cover_path)
            if ok:
                print(f"  [OK] Poster SERIE TV saved in: {cover_path}")
            else:
                print("  [ERROR] Unable to save poster file SERIE TV.")
        else:
            print("  [WARN] No poster available for this SERIE TV.")
    else:
        print("  [WARN] No results found, FILM or SERIE TV.")


def main():
    if not TMDB_API_KEY or TMDB_API_KEY == "INSERISCI_LA_TUA_API_KEY_TMBD_QUI":
        print("ERROR: set TMDB_API_KEY into the script before start.")
        return

    print(f"Scan for: {ROOT_DIR}")
    for dirpath, dirnames, filenames in os.walk(ROOT_DIR):
        dirnames[:] = [d for d in dirnames if d not in EXCLUDED_DIRS]
        for filename in filenames:
            if is_video_file(filename):
                filepath = os.path.join(dirpath, filename)
                process_video_file(filepath)


if __name__ == "__main__":
    main()
