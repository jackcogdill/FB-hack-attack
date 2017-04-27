import subprocess
import threading

import sys

# Code lovingly taken from
# http://stackoverflow.com/a/6001858/1313757
class RunCmd(threading.Thread):
	def __init__(self, cmd, timeout):
		threading.Thread.__init__(self)
		self.cmd = cmd
		self.timeout = timeout

	def run(self):
		self.p = subprocess.Popen(self.cmd)
		self.p.wait()

	def Run(self):
		self.start()
		self.join(self.timeout)

		if self.is_alive():
			# self.p.terminate()      #use self.p.kill() if process needs a kill -9
			self.p.kill()
			self.join()


seconds = float(sys.argv[1])
RunCmd(sys.argv[2:], seconds).Run()
