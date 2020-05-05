
// policy that allows user to create tokens that allow their bearer to read-cipher 

path "auth/token/create/read-cipher" {
	policy = "write"
	allowed_parameters = {
		min_wrapping_ttl = [ "1s" ]
	}
}
