Date.prototype.getHoursTwoDigits = function()
{
    var retval = this.getHours();
    if (retval < 10)
    {
        return ("0" + retval.toString());
    }
    else
    {
        return retval.toString();
    }
};
Date.prototype.getSecondsTwoDigits = function()
{
    var retval = this.getSeconds();
    if (retval < 10)
    {
        return ("0" + retval.toString());
    }
    else
    {
        return retval.toString();
    }
};
Date.prototype.getMinutesTwoDigits = function()
{
    var retval = this.getMinutes();
    if (retval < 10)
    {
        return ("0" + retval.toString());
    }
    else
    {
        return retval.toString();
    }
};
Math.persent = function(nominator, denominator)
{
	return this.round((nominator / denominator) * 100);
}